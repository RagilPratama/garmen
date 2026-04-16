<?php

namespace App\Http\Controllers;

use App\Models\ProsesFinishing;
use App\Models\BarangMasukKantor;
use Inertia\Inertia;

class BarangSiapKirimController extends Controller
{
    public function index()
    {
        // Total pcs_barang_jadi per model dari proses finishing
        $finishing = ProsesFinishing::selectRaw('model, SUM(pcs_barang_jadi) as total_jadi')
            ->whereNotNull('pcs_barang_jadi')
            ->where('pcs_barang_jadi', '>', 0)
            ->groupBy('model')
            ->get()
            ->keyBy('model');

        // Total yang sudah masuk kantor per model
        $masukKantor = BarangMasukKantor::selectRaw('model, SUM(pcs_barang_jadi) as total_masuk')
            ->groupBy('model')
            ->get()
            ->keyBy('model');

        // Hitung sisa (siap kirim) = finishing - masuk kantor
        $data = $finishing->map(function ($row) use ($masukKantor) {
            $totalJadi  = (int) $row->total_jadi;
            $totalMasuk = (int) ($masukKantor->get($row->model)?->total_masuk ?? 0);
            $siapKirim  = $totalJadi - $totalMasuk;

            return [
                'model'       => $row->model,
                'total_jadi'  => $totalJadi,
                'sudah_kirim' => $totalMasuk,
                'siap_kirim'  => max(0, $siapKirim),
            ];
        })
        ->filter(fn ($r) => $r['total_jadi'] > 0)
        ->sortBy('model')
        ->values();

        $summary = [
            'total_model'      => $data->count(),
            'total_siap_kirim' => $data->sum('siap_kirim'),
            'total_sudah_kirim'=> $data->sum('sudah_kirim'),
            'total_jadi'       => $data->sum('total_jadi'),
        ];

        return Inertia::render('BarangSiapKirim/Index', [
            'barangSiapKirim' => $data,
            'summary'         => $summary,
        ]);
    }
}
