<?php

namespace App\Http\Controllers;

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

        BahanMasukPembayaran::create(array_merge($validated, ['no_nota' => $noNota]));

        return redirect()->route('bahan-masuk.index')->with('message', 'Pembayaran berhasil dicatat.');
    }
}
