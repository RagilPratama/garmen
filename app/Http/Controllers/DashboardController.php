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

        // Pipeline produksi — replikasi logika TrackingPo (current_stage per PO+model, exclude selesai)
        $trackingCombos = collect();
        foreach ([BahanProsesPotong::class, ProsesJahit::class, ProsesCuci::class, ProsesFinishing::class] as $m) {
            $m::select('po', 'model')->distinct()->whereNotNull('po')->where('po', '!=', '')->get()
                ->each(function ($row) use (&$trackingCombos) {
                    $key = $row->po . '|||' . $row->model;
                    if (!$trackingCombos->has($key)) $trackingCombos->put($key, ['po' => $row->po, 'model' => $row->model]);
                });
        }
        $tpotong    = BahanProsesPotong::select('po', 'model', 'hasil_potongan')->get()->groupBy(fn($r) => $r->po.'|||'.$r->model);
        $tjahit     = ProsesJahit::select('po', 'model', 'tanggal_selesai_jahit')->get()->groupBy(fn($r) => $r->po.'|||'.$r->model);
        $tcuci      = ProsesCuci::select('po', 'model', 'tanggal_kembali_dari_cuci')->get()->groupBy(fn($r) => $r->po.'|||'.$r->model);
        $tfinishing = ProsesFinishing::select('po', 'model', 'tanggal_selesai')->get()->groupBy(fn($r) => $r->po.'|||'.$r->model);
        $stageOrder = ['potong', 'jahit', 'cuci', 'finishing'];
        $pipeline   = ['potong' => 0, 'jahit' => 0, 'cuci' => 0, 'finishing' => 0];
        foreach ($trackingCombos->values() as $combo) {
            $key = $combo['po'].'|||'.$combo['model'];
            $p = $tpotong->get($key); $j = $tjahit->get($key); $c = $tcuci->get($key); $f = $tfinishing->get($key);
            $stages = [
                'potong'    => $p ? ($p->filter(fn($r) => $r->hasil_potongan > 0)->isNotEmpty() ? 'done' : 'active') : 'pending',
                'jahit'     => $j ? ($j->filter(fn($r) => $r->tanggal_selesai_jahit !== null)->isNotEmpty() ? 'done' : 'active') : 'pending',
                'cuci'      => $c ? ($c->filter(fn($r) => $r->tanggal_kembali_dari_cuci !== null)->isNotEmpty() ? 'done' : 'active') : 'pending',
                'finishing' => $f ? ($f->filter(fn($r) => $r->tanggal_selesai !== null)->isNotEmpty() ? 'done' : 'active') : 'pending',
            ];
            if ($stages['finishing'] === 'done') continue; // selesai, skip
            $currentStage = 'potong';
            foreach (array_reverse($stageOrder) as $s) {
                if ($stages[$s] === 'active') { $currentStage = $s; break; }
                if ($stages[$s] === 'done') {
                    $idx = array_search($s, $stageOrder);
                    $currentStage = $stageOrder[$idx + 1] ?? $s;
                    break;
                }
            }
            if (isset($pipeline[$currentStage])) $pipeline[$currentStage]++;
        }

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
        $recentBahanMasuk = BahanMasuk::latest()->take(5)->get(['id', 'supplier', 'kode_bahan', 'yard', 'created_at']);

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
