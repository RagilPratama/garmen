<?php

namespace App\Http\Controllers;

use App\Models\KasGudang;
use App\Models\KasGarmen;
use App\Models\SaldoKas;
use App\Models\Rekening;
use App\Models\KasTransferGudangGarmen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class KasGudangController extends Controller
{
    public function index(Request $request)
    {
        $query = KasGudang::with('user');

        // Filter tanggal
        if ($request->tanggal_dari) {
            $query->where('tanggal', '>=', $request->tanggal_dari);
        }
        if ($request->tanggal_sampai) {
            $query->where('tanggal', '<=', $request->tanggal_sampai);
        }

        // Filter jenis
        if ($request->jenis) {
            $query->where('jenis', $request->jenis);
        }

        $kasData = $query->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(20);

        $saldoKas = SaldoKas::where('entitas', 'gudang')->first();

        return Inertia::render('KasGudang/Index', [
            'kasData' => $kasData,
            'saldoKas' => $saldoKas ? [
                'saldo_cash' => (float) $saldoKas->saldo_cash,
                'saldo_transfer' => (float) $saldoKas->saldo_transfer,
                'saldo_debit' => (float) $saldoKas->saldo_debit,
            ] : [
                'saldo_cash' => 0,
                'saldo_transfer' => 0,
                'saldo_debit' => 0,
            ],
            'rekenings' => Rekening::all(),
            'filters' => $request->only(['tanggal_dari', 'tanggal_sampai', 'jenis']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'jenis' => 'required|in:masuk,keluar',
            'metode_bayar' => 'required|in:cash,transfer,debit',
            'rekening_id' => 'nullable|exists:rekening,id',
            'kategori' => 'required|string|max:255',
            'jumlah' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated) {
            $saldoKas = SaldoKas::where('entitas', 'gudang')->lockForUpdate()->first();

            $metodeColumn = 'saldo_' . $validated['metode_bayar'];
            $saldoSebelum = $saldoKas->$metodeColumn;

            // Validasi saldo untuk transaksi keluar
            if ($validated['jenis'] === 'keluar' && $saldoSebelum < $validated['jumlah']) {
                throw new \Exception('Saldo ' . $validated['metode_bayar'] . ' tidak mencukupi');
            }

            // Update saldo
            if ($validated['jenis'] === 'masuk') {
                $saldoKas->$metodeColumn += $validated['jumlah'];
            } else {
                $saldoKas->$metodeColumn -= $validated['jumlah'];
            }
            $saldoKas->save();

            // Catat transaksi
            KasGudang::create([
                'tanggal' => $validated['tanggal'],
                'jenis' => $validated['jenis'],
                'metode_bayar' => $validated['metode_bayar'],
                'rekening_id' => $validated['rekening_id'] ?? null,
                'kategori' => $validated['kategori'],
                'jumlah' => $validated['jumlah'],
                'saldo_sesudah' => $saldoKas->$metodeColumn,
                'keterangan' => $validated['keterangan'],
                'user_id' => auth()->id(),
            ]);
        });

        return redirect()->route('kas-gudang.index')->with('success', 'Transaksi kas gudang berhasil ditambahkan');
    }

    public function destroy(KasGudang $kasGudang)
    {
        DB::transaction(function () use ($kasGudang) {
            $saldoKas = SaldoKas::where('entitas', 'gudang')->lockForUpdate()->first();
            $metodeColumn = 'saldo_' . $kasGudang->metode_bayar;

            // Rollback saldo
            if ($kasGudang->jenis === 'masuk') {
                $saldoKas->$metodeColumn -= $kasGudang->jumlah;
            } else {
                $saldoKas->$metodeColumn += $kasGudang->jumlah;
            }
            $saldoKas->save();

            $kasGudang->delete();
        });

        return redirect()->route('kas-gudang.index')->with('success', 'Transaksi kas gudang berhasil dihapus');
    }

    public function transfer(Request $request)
    {
        $validated = $request->validate([
            'entitas_penerima' => 'required|in:garmen',
            'tanggal' => 'required|date',
            'metode_bayar' => 'required|in:cash,transfer,debit',
            'rekening_id' => 'nullable|exists:rekening,id',
            'jumlah' => 'required|numeric|min:0.01',
            'keterangan' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                // Lock saldo gudang dan garmen
                $saldoGudang = SaldoKas::where('entitas', 'gudang')->lockForUpdate()->first();
                $saldoGarmen = SaldoKas::where('entitas', 'garmen')->lockForUpdate()->first();

                $metodeColumn = 'saldo_' . $validated['metode_bayar'];

                // Validasi saldo gudang mencukupi
                if ($saldoGudang->$metodeColumn < $validated['jumlah']) {
                    throw new \Exception('Transfer gagal! Saldo ' . strtoupper($validated['metode_bayar']) . ' gudang tidak mencukupi. Saldo tersedia: Rp ' . number_format($saldoGudang->$metodeColumn, 0, ',', '.'));
                }

                // Kurangi saldo gudang
                $saldoGudang->$metodeColumn -= $validated['jumlah'];
                $saldoGudang->save();

                // Tambah saldo garmen
                $saldoGarmen->$metodeColumn += $validated['jumlah'];
                $saldoGarmen->save();

                // Catat transaksi keluar di kas gudang
                KasGudang::create([
                    'tanggal' => $validated['tanggal'],
                    'jenis' => 'keluar',
                    'metode_bayar' => $validated['metode_bayar'],
                    'rekening_id' => $validated['rekening_id'] ?? null,
                    'kategori' => 'Transfer ke Garmen',
                    'jumlah' => $validated['jumlah'],
                    'saldo_sesudah' => $saldoGudang->$metodeColumn,
                    'keterangan' => $validated['keterangan'] ?? 'Transfer ke Garmen',
                    'user_id' => auth()->id(),
                ]);

                // Catat transaksi masuk di kas garmen
                KasGarmen::create([
                    'tanggal' => $validated['tanggal'],
                    'jenis' => 'masuk',
                    'metode_bayar' => $validated['metode_bayar'],
                    'rekening_id' => $validated['rekening_id'] ?? null,
                    'kategori' => 'Transfer dari Gudang',
                    'jumlah' => $validated['jumlah'],
                    'saldo_sesudah' => $saldoGarmen->$metodeColumn,
                    'keterangan' => $validated['keterangan'] ?? 'Transfer dari Gudang',
                    'user_id' => auth()->id(),
                ]);

                // Catat di tabel transfer
                KasTransferGudangGarmen::create([
                    'entitas_pengirim' => 'gudang',
                    'entitas_penerima' => $validated['entitas_penerima'],
                    'tanggal' => $validated['tanggal'],
                    'metode_bayar' => $validated['metode_bayar'],
                    'jumlah' => $validated['jumlah'],
                    'keterangan' => $validated['keterangan'],
                    'rekening_id' => $validated['rekening_id'] ?? null,
                    'user_id' => auth()->id(),
                ]);
            });

            return redirect()->route('kas-gudang.index')->with('success', 'Transfer ke garmen berhasil');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
