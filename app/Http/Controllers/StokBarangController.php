<?php

namespace App\Http\Controllers;

use App\Models\BarangMasukKantor;
use App\Models\BarangKirimToko;
use App\Models\ProsesJual;
use App\Models\JualGudang;
use Inertia\Inertia;

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
}
