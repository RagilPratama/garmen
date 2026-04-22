<?php

namespace App\Http\Controllers;

use App\Models\PenjualanPembayaran;
use App\Models\JualGudang;
use App\Models\ProsesJual;
use App\Models\KasToko;
use App\Models\Toko;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenjualanPembayaranController extends Controller
{
    public function store(Request $request, string $noNota)
    {
        $channel = $request->input('channel', 'gudang');

        $validated = $request->validate([
            'tanggal_bayar' => 'required|date',
            'jumlah'        => 'required|numeric|min:1',
            'metode'        => 'required|in:cash,transfer,debit',
            'rekening_id'   => 'nullable|required_if:metode,transfer|exists:rekening,id',
            'keterangan'    => 'nullable|string|max:255',
        ]);

        // Hitung total tagihan & total sudah dibayar
        if ($channel === 'gudang') {
            $grandTotal = JualGudang::where('no_nota', $noNota)->sum('total_harga');
        } else {
            $grandTotal = ProsesJual::where('no_nota', $noNota)->sum('total_harga');
        }
        $totalBayar = PenjualanPembayaran::where('no_nota', $noNota)
            ->where('channel', $channel)->sum('jumlah');
        $sisa = $grandTotal - $totalBayar;

        if ($validated['jumlah'] > $sisa) {
            return back()->withErrors(['jumlah' => 'Jumlah melebihi sisa tagihan (' . number_format($sisa, 0, ',', '.') . ')'])->withInput();
        }

        DB::transaction(function () use ($validated, $noNota, $channel) {
            $pembayaran = PenjualanPembayaran::create(array_merge($validated, [
                'no_nota' => $noNota,
                'channel' => $channel,
            ]));

            // Tambahkan ke kas toko untuk semua metode pembayaran (channel toko)
            if ($channel === 'toko') {
                // Ambil toko_id dari ProsesJual
                $prosesJual = ProsesJual::where('no_nota', $noNota)->first();
                if ($prosesJual && $prosesJual->toko_id) {
                    $toko = Toko::lockForUpdate()->findOrFail($prosesJual->toko_id);
                    $metode = $validated['metode'];
                    $saldoField = 'saldo_' . $metode;
                    
                    $saldoSebelum = $toko->$saldoField;
                    $saldoSesudah = $saldoSebelum + $validated['jumlah'];
                    
                    KasToko::create([
                        'toko_id' => $prosesJual->toko_id,
                        'tanggal' => $validated['tanggal_bayar'],
                        'jenis' => 'masuk',
                        'metode_bayar' => $metode,
                        'kategori' => 'Penjualan',
                        'jumlah' => $validated['jumlah'],
                        'keterangan' => 'Pembayaran cicilan ' . $noNota . ' - ' . $prosesJual->buyer,
                        'referensi' => $noNota,
                        'saldo_sebelum' => $saldoSebelum,
                        'saldo_sesudah' => $saldoSesudah,
                        'user_id' => auth()->id(),
                    ]);
                    
                    $toko->update([
                        $saldoField => $saldoSesudah,
                        'saldo_kas' => $toko->saldo_cash + $toko->saldo_transfer + $toko->saldo_debit + $validated['jumlah']
                    ]);
                }
            }
        });

        $route = $channel === 'gudang' ? 'jual-gudang.index' : 'proses-jual.index';
        return redirect()->route($route)->with('message', 'Pembayaran berhasil dicatat.');
    }

    public function update(Request $request, PenjualanPembayaran $pembayaran)
    {
        $validated = $request->validate([
            'tanggal_bayar' => 'required|date',
            'jumlah'        => 'required|numeric|min:1',
            'metode'        => 'required|in:cash,transfer,debit',
            'keterangan'    => 'nullable|string|max:255',
        ]);

        $pembayaran->update($validated);

        $route = $pembayaran->channel === 'gudang' ? 'jual-gudang.index' : 'proses-jual.index';
        return redirect()->route($route)->with('message', 'Pembayaran berhasil diperbarui.');
    }

    public function destroy(PenjualanPembayaran $pembayaran)
    {
        $channel = $pembayaran->channel;
        
        DB::transaction(function () use ($pembayaran) {
            // Jika ada transaksi kas terkait, hapus dan kembalikan saldo
            if ($pembayaran->channel === 'toko') {
                $kasToko = KasToko::where('referensi', $pembayaran->no_nota)
                    ->where('jumlah', $pembayaran->jumlah)
                    ->where('tanggal', $pembayaran->tanggal_bayar)
                    ->where('metode_bayar', $pembayaran->metode)
                    ->first();
                    
                if ($kasToko) {
                    $toko = Toko::lockForUpdate()->findOrFail($kasToko->toko_id);
                    $metode = $kasToko->metode_bayar;
                    $saldoField = 'saldo_' . $metode;
                    
                    $toko->$saldoField -= $kasToko->jumlah;
                    $toko->saldo_kas -= $kasToko->jumlah;
                    $toko->save();
                    
                    $kasToko->delete();
                }
            }
            
            $pembayaran->delete();
        });

        $route = $channel === 'gudang' ? 'jual-gudang.index' : 'proses-jual.index';
        return redirect()->route($route)->with('message', 'Pembayaran berhasil dihapus.');
    }
}
