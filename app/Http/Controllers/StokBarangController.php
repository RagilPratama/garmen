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
        // Stok Kantor: masuk kantor - kirim ke toko - jual dari gudang, per po+model
        $masukKantor = BarangMasukKantor::selectRaw('po, model, SUM(pcs_barang_jadi) as total')
            ->groupBy('po', 'model')->get()
            ->keyBy(fn($r) => $r->po . '||' . $r->model);

        $kirimToko = BarangKirimToko::selectRaw('po, model, SUM(pcs_barang_jadi) as total')
            ->groupBy('po', 'model')->get()
            ->keyBy(fn($r) => $r->po . '||' . $r->model);

        $jualGudangStok = JualGudang::selectRaw('po, model, SUM(pcs) as total')
            ->whereIn('status', ['lunas', 'pending'])
            ->groupBy('po', 'model')->get()
            ->keyBy(fn($r) => $r->po . '||' . $r->model);

        $allKeys = $masukKantor->keys()->merge($kirimToko->keys())->unique();
        $stokKantor = $allKeys->map(function ($key) use ($masukKantor, $kirimToko, $jualGudangStok) {
            [$po, $model] = explode('||', $key);
            $masuk   = (int) ($masukKantor->get($key)?->total ?? 0);
            $kirim   = (int) ($kirimToko->get($key)?->total ?? 0);
            $terjual = (int) ($jualGudangStok->get($key)?->total ?? 0);
            return [
                'po' => $po, 'model' => $model,
                'masuk_kantor' => $masuk, 'kirim_toko' => $kirim,
                'jual_gudang' => $terjual,
                'sisa_kantor' => $masuk - $kirim - $terjual,
            ];
        })->values()->sortBy('po')->values();

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
