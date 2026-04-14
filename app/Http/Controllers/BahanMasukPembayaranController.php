<?php

namespace App\Http\Controllers;

use App\Models\BahanMasuk;
use App\Models\BahanMasukPembayaran;
use Illuminate\Http\Request;

class BahanMasukPembayaranController extends Controller
{
    public function store(Request $request, string $noNota)
    {
        $validated = $request->validate([
            'tanggal_bayar' => 'required|date',
            'jumlah'        => 'required|numeric|min:1',
            'metode'        => 'required|in:cash,transfer',
            'rekening_id'   => 'nullable|required_if:metode,transfer|exists:rekening,id',
            'keterangan'    => 'nullable|string|max:255',
        ]);

        // Hitung sisa tagihan
        $grandTotal = BahanMasuk::where('no_nota', $noNota)->sum('total');
        $totalBayar = BahanMasukPembayaran::where('no_nota', $noNota)->sum('jumlah');
        $sisa       = $grandTotal - $totalBayar;

        if ($validated['jumlah'] > $sisa) {
            return back()->withErrors(['jumlah' => 'Jumlah pembayaran melebihi sisa tagihan (' . number_format($sisa, 0, ',', '.') . ')'])->withInput();
        }

        BahanMasukPembayaran::create(array_merge($validated, ['no_nota' => $noNota]));

        return redirect()->route('bahan-masuk.index')->with('message', 'Pembayaran berhasil dicatat.');
    }

    public function destroy(BahanMasukPembayaran $pembayaran)
    {
        $pembayaran->delete();
        return redirect()->route('bahan-masuk.index')->with('message', 'Riwayat pembayaran berhasil dihapus.');
    }
}
