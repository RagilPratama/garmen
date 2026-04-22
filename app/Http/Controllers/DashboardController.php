<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\BahanMasuk;
use App\Models\BahanKeluar;
use App\Models\BahanMasukPembayaran;
use App\Models\StokBahan;
use App\Models\BahanProsesPotong;
use App\Models\ProsesJahit;
use App\Models\ProsesCuci;
use App\Models\ProsesFinishing;
use App\Models\BarangMasukKantor;
use App\Models\BarangKirimToko;
use App\Models\ProsesJual;
use App\Models\JualGudang;
use App\Models\PenjualanPembayaran;
use App\Models\PengeluaranToko;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $bulan = now()->month;
        $tahun = now()->year;

        // Filter berdasarkan role user
        $isAdmin = $user->isAdmin();
        $tokoId = $user->isToko() ? $user->toko_id : null;

        // Omset - jika admin lihat semua, jika toko lihat per toko
        if ($isAdmin) {
            $omsetTokoTotal   = ProsesJual::whereIn('status', ['lunas'])->sum('total_harga');
            $omsetGudangTotal = JualGudang::whereIn('status', ['lunas'])->sum('total_harga');
            $omsetTokoBulanIni   = ProsesJual::whereIn('status', ['lunas', 'piutang'])->whereMonth('tanggal_nota', $bulan)->whereYear('tanggal_nota', $tahun)->sum('total_harga');
            $omsetGudangBulanIni = JualGudang::whereIn('status', ['lunas', 'piutang'])->whereMonth('tanggal_nota', $bulan)->whereYear('tanggal_nota', $tahun)->sum('total_harga');
            
            // Omset per toko
            $omsetJomei = ProsesJual::whereHas('toko', fn($q) => $q->where('kode_toko', 'JMI'))
                ->whereIn('status', ['lunas', 'piutang'])
                ->whereMonth('tanggal_nota', $bulan)
                ->whereYear('tanggal_nota', $tahun)
                ->sum('total_harga');
                
            $omsetKamiko = ProsesJual::whereHas('toko', fn($q) => $q->where('kode_toko', 'KMK'))
                ->whereIn('status', ['lunas', 'piutang'])
                ->whereMonth('tanggal_nota', $bulan)
                ->whereYear('tanggal_nota', $tahun)
                ->sum('total_harga');
        } else {
            // User toko hanya lihat data toko mereka
            $omsetTokoTotal   = ProsesJual::where('toko_id', $tokoId)->whereIn('status', ['lunas'])->sum('total_harga');
            $omsetGudangTotal = 0;
            $omsetTokoBulanIni   = ProsesJual::where('toko_id', $tokoId)->whereIn('status', ['lunas', 'piutang'])->whereMonth('tanggal_nota', $bulan)->whereYear('tanggal_nota', $tahun)->sum('total_harga');
            $omsetGudangBulanIni = 0;
            $omsetJomei = 0;
            $omsetKamiko = 0;
        }

        // Stok bahan - hanya admin yang lihat
        $stokBahan = $isAdmin ? StokBahan::where('sisa_stok', '>', 0)->count() : 0;
        $totalSisaBahan = $isAdmin ? StokBahan::sum('sisa_stok') : 0;

        // Hutang bahan masuk - hanya admin
        if ($isAdmin) {
            $hutangRow = \DB::selectOne("
                SELECT
                    COALESCE(SUM(bm.total), 0) - COALESCE(SUM(bp.dibayar), 0) AS sisa_hutang,
                    COUNT(CASE WHEN COALESCE(bp.dibayar, 0) < bm.grand_total THEN 1 END) AS nota_belum_lunas
                FROM (
                    SELECT no_nota, SUM(total) AS grand_total, SUM(total) AS total
                    FROM bahan_masuk
                    GROUP BY no_nota
                ) bm
                LEFT JOIN (
                    SELECT no_nota, SUM(jumlah) AS dibayar
                    FROM bahan_masuk_pembayaran
                    GROUP BY no_nota
                ) bp ON bp.no_nota = bm.no_nota
            ");
            $sisaHutang           = max(0, (float) ($hutangRow->sisa_hutang ?? 0));
            $jumlahNotaBelumLunas = (int) ($hutangRow->nota_belum_lunas ?? 0);
        } else {
            $sisaHutang = 0;
            $jumlahNotaBelumLunas = 0;
        }

        // Piutang penjualan
        if ($isAdmin) {
            $piutangRow = \DB::selectOne("
                SELECT
                    COALESCE(SUM(j.total_harga), 0) - COALESCE(SUM(pp.dibayar), 0) AS sisa_piutang,
                    COUNT(DISTINCT CASE WHEN COALESCE(pp.dibayar, 0) < j.total_nota THEN j.no_nota END) AS nota_belum_lunas
                FROM (
                    SELECT no_nota, 'gudang' as channel, SUM(total_harga) AS total_harga, SUM(total_harga) AS total_nota
                    FROM jual_gudang WHERE status IN ('piutang', 'lunas')
                    GROUP BY no_nota
                    UNION ALL
                    SELECT no_nota, 'toko' as channel, SUM(total_harga) AS total_harga, SUM(total_harga) AS total_nota
                    FROM proses_jual WHERE status IN ('piutang', 'lunas')
                    GROUP BY no_nota
                ) j
                LEFT JOIN (
                    SELECT no_nota, channel, SUM(jumlah) AS dibayar
                    FROM penjualan_pembayaran
                    GROUP BY no_nota, channel
                ) pp ON pp.no_nota = j.no_nota AND pp.channel = j.channel
            ");
            $sisaPiutang       = max(0, (float) ($piutangRow->sisa_piutang ?? 0));
            $jumlahNotaPiutang = (int) ($piutangRow->nota_belum_lunas ?? 0);
        } else {
            // Piutang toko spesifik
            $piutangToko = ProsesJual::where('toko_id', $tokoId)
                ->whereIn('status', ['piutang', 'lunas'])
                ->selectRaw('no_nota, SUM(total_harga) as total_nota')
                ->groupBy('no_nota')
                ->get();
            
            $pembayaranToko = PenjualanPembayaran::where('channel', 'toko')
                ->whereIn('no_nota', $piutangToko->pluck('no_nota'))
                ->selectRaw('no_nota, SUM(jumlah) as dibayar')
                ->groupBy('no_nota')
                ->pluck('dibayar', 'no_nota');
            
            $sisaPiutang = $piutangToko->sum(fn($r) => max(0, $r->total_nota - ($pembayaranToko[$r->no_nota] ?? 0)));
            $jumlahNotaPiutang = $piutangToko->filter(fn($r) => ($pembayaranToko[$r->no_nota] ?? 0) < $r->total_nota)->count();
        }

        // Stok kantor & toko
        if ($isAdmin) {
            $masukKantor = BarangMasukKantor::selectRaw('model, SUM(pcs_barang_jadi) as total')->groupBy('model')->get()->keyBy('model');
            $jualGudang  = JualGudang::selectRaw('model, SUM(pcs) as total')->whereIn('status', ['lunas', 'piutang'])->groupBy('model')->get()->keyBy('model');
            $kirimToko   = BarangKirimToko::selectRaw('model, SUM(pcs_barang_jadi) as total')->groupBy('model')->get()->keyBy('model');
            
            // Stok kantor = masuk kantor - jual gudang - kirim toko
            $stokKantor  = $masukKantor->sum(fn($r) => max(0, 
                (int)$r->total 
                - (int)($jualGudang->get($r->model)?->total ?? 0)
                - (int)($kirimToko->get($r->model)?->total ?? 0)
            ));

            $jualToko   = ProsesJual::selectRaw('model, SUM(pcs) as total')->whereIn('status', ['lunas', 'piutang'])->groupBy('model')->get()->keyBy('model');
            $stokToko   = $kirimToko->sum(fn($r) => max(0, (int)$r->total - (int)($jualToko->get($r->model)?->total ?? 0)));
        } else {
            $stokKantor = 0;
            $kirimToko  = BarangKirimToko::where('toko_id', $tokoId)->selectRaw('model, SUM(pcs_barang_jadi) as total')->groupBy('model')->get()->keyBy('model');
            $jualToko   = ProsesJual::where('toko_id', $tokoId)->selectRaw('model, SUM(pcs) as total')->whereIn('status', ['lunas', 'piutang'])->groupBy('model')->get()->keyBy('model');
            $stokToko   = $kirimToko->sum(fn($r) => max(0, (int)$r->total - (int)($jualToko->get($r->model)?->total ?? 0)));
        }

        // Pipeline produksi - hanya admin
        $pipeline = ['potong' => 0, 'jahit' => 0, 'cuci' => 0, 'finishing' => 0];
        if ($isAdmin) {
            $tpotong    = BahanProsesPotong::select('po', 'model', 'hasil_potongan')->get()->groupBy(fn($r) => $r->po.'|||'.$r->model);
            $tjahit     = ProsesJahit::select('po', 'model', 'tanggal_selesai_jahit')->get()->groupBy(fn($r) => $r->po.'|||'.$r->model);
            $tcuci      = ProsesCuci::select('po', 'model', 'tanggal_kembali_dari_cuci')->get()->groupBy(fn($r) => $r->po.'|||'.$r->model);
            $tfinishing = ProsesFinishing::select('po', 'model', 'tanggal_selesai')->get()->groupBy(fn($r) => $r->po.'|||'.$r->model);

            $trackingKeys = $tpotong->keys()->concat($tjahit->keys())->concat($tcuci->keys())->concat($tfinishing->keys())->unique();

            $stageOrder = ['potong', 'jahit', 'cuci', 'finishing'];
            foreach ($trackingKeys as $key) {
                $p = $tpotong->get($key); $j = $tjahit->get($key); $c = $tcuci->get($key); $f = $tfinishing->get($key);
                $stages = [
                    'potong'    => $p ? ($p->filter(fn($r) => $r->hasil_potongan > 0)->isNotEmpty() ? 'done' : 'active') : 'pending',
                    'jahit'     => $j ? ($j->filter(fn($r) => $r->tanggal_selesai_jahit !== null)->isNotEmpty() ? 'done' : 'active') : 'pending',
                    'cuci'      => $c ? ($c->filter(fn($r) => $r->tanggal_kembali_dari_cuci !== null)->isNotEmpty() ? 'done' : 'active') : 'pending',
                    'finishing' => $f ? ($f->filter(fn($r) => $r->tanggal_selesai !== null)->isNotEmpty() ? 'done' : 'active') : 'pending',
                ];
                if ($stages['finishing'] === 'done') continue;
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
        }

        // Top model terlaris
        if ($isAdmin) {
            $topToko   = ProsesJual::selectRaw('model, SUM(pcs) as total_pcs, SUM(total_harga) as total_omset')
                ->whereIn('status', ['lunas', 'piutang'])->groupBy('model')->get()
                ->map(fn($r) => ['model' => $r->model, 'total_pcs' => (int)$r->total_pcs, 'total_omset' => (float)$r->total_omset]);
            $topGudang = JualGudang::selectRaw('model, SUM(pcs) as total_pcs, SUM(total_harga) as total_omset')
                ->whereIn('status', ['lunas', 'piutang'])->groupBy('model')->get()
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
        } else {
            $topModels = ProsesJual::where('toko_id', $tokoId)
                ->selectRaw('model, SUM(pcs) as total_pcs, SUM(total_harga) as total_omset')
                ->whereIn('status', ['lunas', 'piutang'])
                ->groupBy('model')
                ->orderByDesc('total_pcs')
                ->take(5)
                ->get()
                ->map(fn($r) => ['model' => $r->model, 'total_pcs' => (int)$r->total_pcs, 'total_omset' => (float)$r->total_omset]);
        }

        // Recent data
        $recentBahanMasuk = $isAdmin ? BahanMasuk::latest()->take(5)->get(['id', 'supplier', 'kode_bahan', 'yard', 'created_at']) : collect();

        if ($isAdmin) {
            $recentPenjualan = ProsesJual::with('toko')->latest()->take(5)->get(['id', 'buyer', 'model', 'pcs', 'total_harga', 'status', 'tanggal_nota', 'toko_id'])
                ->map(fn($r) => array_merge($r->toArray(), ['channel' => $r->toko?->nama_toko ?? 'Toko']))
                ->concat(
                    JualGudang::latest()->take(5)->get(['id', 'buyer', 'model', 'pcs', 'total_harga', 'status', 'tanggal_nota'])
                        ->map(fn($r) => array_merge($r->toArray(), ['channel' => 'Gudang']))
                )
                ->sortByDesc('tanggal_nota')->take(8)->values();
        } else {
            $recentPenjualan = ProsesJual::where('toko_id', $tokoId)
                ->latest()
                ->take(8)
                ->get(['id', 'buyer', 'model', 'pcs', 'total_harga', 'status', 'tanggal_nota'])
                ->map(fn($r) => array_merge($r->toArray(), ['channel' => $user->toko->nama_toko]))
                ->values();
        }

        // Pengeluaran toko (untuk user toko)
        $pengeluaranTokoBulanIni = 0;
        if (!$isAdmin && $tokoId) {
            $pengeluaranTokoBulanIni = PengeluaranToko::where('toko_id', $tokoId)
                ->whereMonth('tanggal', now()->month)
                ->whereYear('tanggal', now()->year)
                ->sum('jumlah');
        }

        return Inertia::render('Dashboard', [
            'sisaHutang'           => (float) $sisaHutang,
            'jumlahNotaBelumLunas' => (int) $jumlahNotaBelumLunas,
            'sisaPiutang'          => (float) $sisaPiutang,
            'jumlahNotaPiutang'    => (int) $jumlahNotaPiutang,
            'omsetTokoTotal'       => (float) $omsetTokoTotal,
            'omsetGudangTotal'     => (float) $omsetGudangTotal,
            'omsetTokoBulanIni'    => (float) $omsetTokoBulanIni,
            'omsetGudangBulanIni'  => (float) $omsetGudangBulanIni,
            'omsetJomei'           => (float) ($omsetJomei ?? 0),
            'omsetKamiko'          => (float) ($omsetKamiko ?? 0),
            'stokBahan'            => (int) $stokBahan,
            'totalSisaBahan'       => (float) $totalSisaBahan,
            'stokKantor'           => (int) $stokKantor,
            'stokToko'             => (int) $stokToko,
            'pipeline'             => $pipeline,
            'recentBahanMasuk'     => $recentBahanMasuk,
            'recentPenjualan'      => $recentPenjualan,
            'topModels'            => $topModels,
            'isAdmin'              => $isAdmin,
            'userToko'             => $user->toko?->nama_toko,
            'pengeluaranTokoBulanIni' => (float) $pengeluaranTokoBulanIni,
        ]);
    }
}
