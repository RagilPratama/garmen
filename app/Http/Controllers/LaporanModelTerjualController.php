<?php

namespace App\Http\Controllers;

use App\Models\JualGudang;
use App\Models\ProsesJual;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class LaporanModelTerjualController extends Controller
{
    public function index()
    {
        $search    = request('search');
        $startDate = request('start_date');
        $endDate   = request('end_date');

        // Penjualan dari Gudang per model
        $gudang = JualGudang::selectRaw('model, SUM(pcs) as pcs_gudang, SUM(total_harga) as omset_gudang')
            ->when($search,    fn($q) => $q->where('model', 'like', "%$search%"))
            ->when($startDate, fn($q) => $q->whereDate('tanggal_nota', '>=', $startDate))
            ->when($endDate,   fn($q) => $q->whereDate('tanggal_nota', '<=', $endDate))
            ->groupBy('model')
            ->get()
            ->keyBy('model');

        // Penjualan dari Toko per model
        $toko = ProsesJual::selectRaw('model, SUM(pcs) as pcs_toko, SUM(total_harga) as omset_toko')
            ->when($search,    fn($q) => $q->where('model', 'like', "%$search%"))
            ->when($startDate, fn($q) => $q->whereDate('tanggal_nota', '>=', $startDate))
            ->when($endDate,   fn($q) => $q->whereDate('tanggal_nota', '<=', $endDate))
            ->groupBy('model')
            ->get()
            ->keyBy('model');

        // Gabungkan semua model unik
        $allModels = $gudang->keys()->merge($toko->keys())->unique()->sort()->values();

        $data = $allModels->map(function ($model) use ($gudang, $toko) {
            $pcsGudang   = (int) ($gudang->get($model)?->pcs_gudang   ?? 0);
            $pcsToko     = (int) ($toko->get($model)?->pcs_toko       ?? 0);
            $omsetGudang = (float) ($gudang->get($model)?->omset_gudang ?? 0);
            $omsetToko   = (float) ($toko->get($model)?->omset_toko    ?? 0);

            return [
                'model'        => $model,
                'pcs_gudang'   => $pcsGudang,
                'pcs_toko'     => $pcsToko,
                'total_pcs'    => $pcsGudang + $pcsToko,
                'omset_gudang' => $omsetGudang,
                'omset_toko'   => $omsetToko,
                'total_omset'  => $omsetGudang + $omsetToko,
            ];
        })->sortByDesc('total_pcs')->values();

        $summary = [
            'total_model'      => $data->count(),
            'total_pcs'        => $data->sum('total_pcs'),
            'total_pcs_gudang' => $data->sum('pcs_gudang'),
            'total_pcs_toko'   => $data->sum('pcs_toko'),
            'total_omset'      => $data->sum('total_omset'),
        ];

        return Inertia::render('LaporanModelTerjual/Index', [
            'data'    => $data,
            'summary' => $summary,
        ]);
    }

    public function exportData()
    {
        request()->merge(['export' => true]);
        return $this->index();
    }
}
