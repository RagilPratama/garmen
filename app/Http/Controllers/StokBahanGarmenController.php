<?php

namespace App\Http\Controllers;

use App\Models\BahanKeluar;
use App\Models\BahanMasuk;
use App\Models\BahanProsesPotong;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class StokBahanGarmenController extends Controller
{
    public function index()
    {
        $search = request('search');

        $namaBahan = BahanMasuk::select('kode_bahan', DB::raw('MAX(nama_bahan) as nama_bahan'))
            ->groupBy('kode_bahan')->pluck('nama_bahan', 'kode_bahan');

        // Total yard already used in cutting (per kode_bahan)
        $sudahDipotong = BahanProsesPotong::select('kode_bahan', DB::raw('SUM(yard) as total_potong'))
            ->groupBy('kode_bahan')->pluck('total_potong', 'kode_bahan');

        // Grouped summary per kode_bahan from bahan_keluar
        $grouped = BahanKeluar::select(
                'kode_bahan',
                DB::raw('COUNT(*) as jumlah_transaksi'),
                DB::raw('SUM(yard) as total_yard'),
                DB::raw('SUM(total) as total_nilai'),
                DB::raw('MAX(tanggal) as last_keluar')
            )
            ->groupBy('kode_bahan')
            ->orderBy('kode_bahan')
            ->get()
            ->map(function ($row) use ($namaBahan, $sudahDipotong) {
                $totalKeluar         = (float) $row->total_yard;
                $terpakai            = (float) ($sudahDipotong[$row->kode_bahan] ?? 0);
                $row->nama_bahan     = $namaBahan[$row->kode_bahan] ?? null;
                $row->sisa_stok      = max(0, $totalKeluar - $terpakai);
                $row->total_yard     = $totalKeluar;
                $row->total_nilai    = (float) $row->total_nilai;
                $row->sudah_dipotong = $terpakai;
                return $row;
            });

        // Apply search
        if ($search) {
            $s = strtolower($search);
            $grouped = $grouped->filter(fn($r) =>
                str_contains(strtolower($r->kode_bahan), $s) ||
                str_contains(strtolower($r->nama_bahan ?? ''), $s)
            )->values();
        }

        $stats = [
            'total_kode'    => $grouped->count(),
            'total_yard'    => (float) $grouped->sum('total_yard'),
            'total_nilai'   => (float) $grouped->sum('total_nilai'),
            'total_transaksi' => (int) $grouped->sum('jumlah_transaksi'),
        ];

        $page    = max(1, (int) request('page', 1));
        $perPage = 20;
        $total   = $grouped->count();
        $items   = $grouped->slice(($page - 1) * $perPage, $perPage)->values();
        $data    = new \Illuminate\Pagination\LengthAwarePaginator(
            $items, $total, $perPage, $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return Inertia::render('StokBahanGarmen/Index', [
            'data'   => $data,
            'stats'  => $stats,
            'search' => $search ?? '',
        ]);
    }

    public function detail()
    {
        $kodeBahan = request('kode_bahan');
        $rows = BahanKeluar::where('kode_bahan', $kodeBahan)
            ->orderByDesc('tanggal')
            ->get(['id', 'tanggal', 'no_surat_jalan', 'yard', 'rp_per_yard', 'total']);
        return response()->json($rows);
    }
}
