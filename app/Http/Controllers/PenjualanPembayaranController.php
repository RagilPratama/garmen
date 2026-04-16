<?php

namespace App\Http\Controllers;

use App\Models\PenjualanPembayaran;
use App\Models\JualGudang;
use App\Models\ProsesJual;
use Illuminate\Http\Request;

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

        PenjualanPembayaran::create(array_merge($validated, [
            'no_nota' => $noNota,
            'channel' => $channel,
        ]));

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
        $pembayaran->delete();

        $route = $channel === 'gudang' ? 'jual-gudang.index' : 'proses-jual.index';
        return redirect()->route($route)->with('message', 'Pembayaran berhasil dihapus.');
    }
}
