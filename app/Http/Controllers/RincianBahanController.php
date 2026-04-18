<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class RincianBahanController extends Controller
{
    public function index()
    {
        $search = request('search');
        $tab    = request('tab', 'masuk');

        if ($tab === 'keluar') {
            $rowsQuery = DB::table('bahan_keluar')
                ->select('id', 'tanggal', 'no_surat_jalan', 'kode_bahan', 'yard', 'rp_per_yard', 'total')
                ->when($search, fn($q) => $q->where(fn($q) => $q
                    ->where('kode_bahan',       'like', "%{$search}%")
                    ->orWhere('no_surat_jalan', 'like', "%{$search}%")
                ))
                ->orderByDesc('created_at');

            $summary = DB::table('bahan_keluar')
                ->selectRaw('kode_bahan, SUM(yard) as total_yard, SUM(total) as total_nilai')
                ->groupBy('kode_bahan')
                ->orderBy('kode_bahan')
                ->get();
        } else {
            $rowsQuery = DB::table('bahan_masuk')
                ->select('id', 'tanggal', 'no_nota', 'no_surat_jalan', 'supplier', 'kode_bahan', 'nama_bahan', 'yard', 'rp_per_yard', 'total')
                ->when($search, fn($q) => $q->where(fn($q) => $q
                    ->where('kode_bahan',       'like', "%{$search}%")
                    ->orWhere('no_nota',         'like', "%{$search}%")
                    ->orWhere('no_surat_jalan',  'like', "%{$search}%")
                    ->orWhere('supplier',        'like', "%{$search}%")
                ))
                ->orderByDesc('created_at');

            $summary = DB::table('bahan_masuk')
                ->selectRaw('kode_bahan, SUM(yard) as total_yard, SUM(total) as total_nilai')
                ->groupBy('kode_bahan')
                ->orderBy('kode_bahan')
                ->get();
        }

        $rows = $rowsQuery->paginate(20)->withQueryString();

        return Inertia::render('RincianBahan/Index', [
            'rows'    => $rows,
            'summary' => $summary,
            'tab'     => $tab,
        ]);
    }
}
