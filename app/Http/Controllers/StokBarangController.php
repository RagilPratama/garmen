<?php

namespace App\Http\Controllers;

use App\Models\BarangMasukKantor;
use App\Models\BarangKirimToko;
use App\Models\ProsesJual;
use App\Models\JualGudang;
use App\Models\BarangKirimToko as BarangKirimTokoModel;
use Inertia\Inertia;
use Illuminate\Http\Request;

class StokBarangController extends Controller
{
    public function index()
    {
        // Stok Kantor: masuk kantor - kirim ke toko - jual dari gudang, per model saja
        $masukKantor = BarangMasukKantor::selectRaw('model, SUM(pcs_barang_jadi) as total')
            ->groupBy('model')->get()->keyBy('model');

        $kirimToko = BarangKirimToko::selectRaw('model, SUM(pcs_barang_jadi) as total')
            ->groupBy('model')->get()->keyBy('model');

        $jualGudangStok = JualGudang::selectRaw('model, SUM(pcs) as total')
            ->whereIn('status', ['lunas', 'pending'])
            ->groupBy('model')->get()->keyBy('model');

        $allModelsKantor = $masukKantor->keys()->merge($kirimToko->keys())->merge($jualGudangStok->keys())->unique();
        $stokKantor = $allModelsKantor->map(function ($model) use ($masukKantor, $kirimToko, $jualGudangStok) {
            $masuk   = (int) ($masukKantor->get($model)?->total ?? 0);
            $kirim   = (int) ($kirimToko->get($model)?->total ?? 0);
            $terjual = (int) ($jualGudangStok->get($model)?->total ?? 0);
            return [
                'model' => $model,
                'masuk_kantor' => $masuk,
                'kirim_toko' => $kirim,
                'jual_gudang' => $terjual,
                'sisa_kantor' => $masuk - $kirim - $terjual,
            ];
        })->values()->sortBy('model')->values();

        // Stok Toko: kirim ke toko - jual dari toko (proses_jual), per model
        $kirimTokoModel = BarangKirimToko::selectRaw('model, SUM(pcs_barang_jadi) as total')
            ->groupBy('model')->get()->keyBy('model');

        $terjualToko = ProsesJual::selectRaw('model, SUM(pcs) as total')
            ->whereIn('status', ['lunas', 'pending'])
            ->groupBy('model')->get()->keyBy('model');

        $allModels = $kirimTokoModel->keys()->merge($terjualToko->keys())->unique();
        $stokToko = $allModels->map(function ($model) use ($kirimTokoModel, $terjualToko) {
            $dikirim = (int) ($kirimTokoModel->get($model)?->total ?? 0);
            $terjual = (int) ($terjualToko->get($model)?->total ?? 0);
            return ['model' => $model, 'dikirim_toko' => $dikirim, 'terjual' => $terjual, 'sisa_toko' => $dikirim - $terjual];
        })->values()->sortBy('model')->values();

        // Omset per channel
        $omsetToko = ProsesJual::whereIn('status', ['lunas', 'pending'])->sum('total_harga');
        $omsetGudang = JualGudang::whereIn('status', ['lunas', 'pending'])->sum('total_harga');

        return Inertia::render('StokBarang/Index', [
            'stokKantor'  => $stokKantor,
            'stokToko'    => $stokToko,
            'omsetToko'   => (float) $omsetToko,
            'omsetGudang' => (float) $omsetGudang,
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'model' => 'required|string',
            'sisa_toko' => 'required|integer|min:0',
        ]);

        $model = $request->model;
        $newSisaToko = $request->sisa_toko;

        $kirimTokoData = BarangKirimToko::selectRaw('model, SUM(pcs_barang_jadi) as total')
            ->groupBy('model')->get()->keyBy('model');
        $dikirim = (int) ($kirimTokoData->get($model)?->total ?? 0);

        $terjualTokoData = ProsesJual::selectRaw('model, SUM(pcs) as total')
            ->whereIn('status', ['lunas', 'pending'])
            ->groupBy('model')->get()->keyBy('model');
        $terjual = (int) ($terjualTokoData->get($model)?->total ?? 0);

        $selisih = $dikirim - $terjual;
        $adjustment = $newSisaToko - $selisih;

        if ($adjustment !== 0) {
            BarangKirimTokoModel::create([
                'po' => 'ADJ-' . now()->format('Ymd') . '-' . $model,
                'model' => $model,
                'pcs_barang_jadi' => abs($adjustment),
                'tanggal_kirim' => now()->format('Y-m-d'),
                'keterangan' => 'Adjustment stok toko: ' . ($adjustment > 0 ? '+' : '') . $adjustment,
            ]);
        }

        return redirect()->route('stok-barang.index')->with('success', 'Stok berhasil diupdate');
    }
}
