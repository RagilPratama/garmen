<?php

namespace App\Http\Controllers;

use App\Models\StokBahan;
use App\Models\BahanMasuk;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class StokBahanController extends Controller
{
    public function index()
    {
        // Pull the most recent nama_bahan per kode_bahan from bahan_masuk
        $namaBahan = BahanMasuk::select('kode_bahan', DB::raw('MAX(nama_bahan) as nama_bahan'))
            ->groupBy('kode_bahan')
            ->pluck('nama_bahan', 'kode_bahan');

        $data = StokBahan::orderBy('kode_bahan')->paginate(50);

        $data->getCollection()->transform(function ($item) use ($namaBahan) {
            $item->nama_bahan = $namaBahan[$item->kode_bahan] ?? null;
            return $item;
        });

        return Inertia::render('StokBahan/Index', ['data' => $data]);
    }
}
