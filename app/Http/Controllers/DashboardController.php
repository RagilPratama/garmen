<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\BahanMasuk;
use App\Models\BahanKeluar;
use App\Models\StokBahan;
use App\Models\BahanProsesPotong;
use App\Models\ProsesJahit;
use App\Models\ProsesCuci;
use App\Models\ProsesFinishing;
use App\Models\BarangMasukKantor;
use App\Models\BarangKirimToko;
use App\Models\ProsesJual;
use App\Models\JualGudang;

class DashboardController extends Controller
{
    public function index()
    {
        $bulan = now()->month;
        $tahun = now()->year;

        // Omset
        $omsetTokoTotal   = ProsesJual::whereIn('status', ['lunas'])->sum('total_harga');
        $omsetGudangTotal = JualGudang::whereIn('status', ['lunas'])->sum('total_harga');
        $omsetTokoBulanIni   = ProsesJual::whereIn('status', ['lunas', 'pending'])->whereMonth('tanggal_nota', $bulan)->whereYear('tanggal_nota', $tahun)->sum('total_harga');
        $omsetGudangBulanIni = JualGudang::whereIn('status', ['lunas', 'pending'])->whereMonth('tanggal_nota', $bulan)->whereYear('tanggal_nota', $tahun)->sum('total_harga');

        // Stok bahan
        $stokBahan = StokBahan::where('sisa_stok', '>', 0)->count();
        $totalSisaBahan = StokBahan::sum('sisa_stok');

        // Stok kantor: masuk kantor - jual gudang per model
        $masukKantor = BarangMasukKantor::selectRaw('model, SUM(pcs_barang_jadi) as total')->groupBy('model')->get()->keyBy('model');
        $jualGudang  = JualGudang::selectRaw('model, SUM(pcs) as total')->whereIn('status', ['lunas', 'pending'])->groupBy('model')->get()->keyBy('model');
        $stokKantor  = $masukKantor->sum(fn($r) => max(0, (int)$r->total - (int)($jualGudang->get($r->model)?->total ?? 0)));

        // Stok toko: kirim toko - jual toko per model
        $kirimToko  = BarangKirimToko::selectRaw('model, SUM(pcs_barang_jadi) as total')->groupBy('model')->get()->keyBy('model');
        $jualToko   = ProsesJual::selectRaw('model, SUM(pcs) as total')->whereIn('status', ['lunas', 'pending'])->groupBy('model')->get()->keyBy('model');
        $stokToko   = $kirimToko->sum(fn($r) => max(0, (int)$r->total - (int)($jualToko->get($r->model)?->total ?? 0)));

        // Pipeline produksi (sedang berjalan / belum selesai)
        $pipeline = [
            'potong'    => BahanProsesPotong::whereNull('hasil_potongan')->orWhere('hasil_potongan', 0)->count(),
            'jahit'     => ProsesJahit::whereNull('tanggal_selesai_jahit')->count(),
            'cuci'      => ProsesCuci::whereNull('tanggal_kembali_dari_cuci')->count(),
            'finishing' => ProsesFinishing::whereNull('tanggal_selesai')->count(),
        ];

        // Top model terlaris (gabungan toko + gudang, berdasarkan pcs terjual)
        $topToko   = ProsesJual::selectRaw('model, SUM(pcs) as total_pcs, SUM(total_harga) as total_omset')
            ->whereIn('status', ['lunas', 'pending'])->groupBy('model')->get()
            ->map(fn($r) => ['model' => $r->model, 'total_pcs' => (int)$r->total_pcs, 'total_omset' => (float)$r->total_omset]);
        $topGudang = JualGudang::selectRaw('model, SUM(pcs) as total_pcs, SUM(total_harga) as total_omset')
            ->whereIn('status', ['lunas', 'pending'])->groupBy('model')->get()
            ->map(fn($r) => ['model' => $r->model, 'total_pcs' => (int)$r->total_pcs, 'total_omset' => (float)$r->total_omset]);
        $topModels = $topToko->concat($topGudang)
            ->groupBy('model')
            ->map(fn($rows, $model) => [
                'model'       => $model,
                'total_pcs'   => $rows->sum('total_pcs'),
                'total_omset' => $rows->sum('total_omset'),
            ])
            ->sortByDesc('total_pcs')
            ->take(5)
            ->values();

        // Recent data
        $recentBahanMasuk = BahanMasuk::latest()->take(5)->get(['id', 'supplier', 'kode_bahan', 'yard', 'status', 'created_at']);

        $recentPenjualan = ProsesJual::latest()->take(5)->get(['id', 'buyer', 'model', 'pcs', 'total_harga', 'status', 'tanggal_nota'])
            ->map(fn($r) => array_merge($r->toArray(), ['channel' => 'Toko']))
            ->concat(
                JualGudang::latest()->take(5)->get(['id', 'buyer', 'model', 'pcs', 'total_harga', 'status', 'tanggal_nota'])
                    ->map(fn($r) => array_merge($r->toArray(), ['channel' => 'Gudang']))
            )
            ->sortByDesc('tanggal_nota')->take(8)->values();

        return Inertia::render('Dashboard', [
            'omsetTokoTotal'      => (float) $omsetTokoTotal,
            'omsetGudangTotal'    => (float) $omsetGudangTotal,
            'omsetTokoBulanIni'   => (float) $omsetTokoBulanIni,
            'omsetGudangBulanIni' => (float) $omsetGudangBulanIni,
            'stokBahan'           => (int) $stokBahan,
            'totalSisaBahan'      => (float) $totalSisaBahan,
            'stokKantor'          => (int) $stokKantor,
            'stokToko'            => (int) $stokToko,
            'pipeline'            => $pipeline,
            'recentBahanMasuk'    => $recentBahanMasuk,
            'recentPenjualan'     => $recentPenjualan,
            'topModels'           => $topModels,
        ]);
    }
}
