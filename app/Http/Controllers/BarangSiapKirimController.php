<?php

namespace App\Http\Controllers;

use App\Models\ProsesFinishing;
use App\Models\BarangMasukKantor;
use App\Models\BarangKirimToko;
use Inertia\Inertia;

class BarangSiapKirimController extends Controller
{
    public function index()
    {
        // Total pcs_barang_jadi per model dari proses finishing (hanya yang sudah selesai)
        $finishing = \DB::table('proses_finishing')
            ->select('model')
            ->selectRaw('SUM(pcs_barang_jadi) as total_jadi')
            ->whereNotNull('pcs_barang_jadi')
            ->where('pcs_barang_jadi', '>', 0)
            ->whereNotNull('tanggal_selesai')
            ->groupBy('model')
            ->get()
            ->keyBy('model');

        // Total yang sudah masuk kantor per model
        $masukKantor = \DB::table('barang_masuk_kantor')
            ->select('model')
            ->selectRaw('SUM(pcs_barang_jadi) as total_masuk')
            ->groupBy('model')
            ->get()
            ->keyBy('model');

        // Total yang sudah kirim ke toko per model (ini dari kantor, bukan dari finishing)
        $kirimToko = \DB::table('barang_kirim_toko')
            ->select('model')
            ->selectRaw('SUM(pcs_barang_jadi) as total_kirim')
            ->groupBy('model')
            ->get()
            ->keyBy('model');

        // Hitung sisa (siap kirim) = finishing - masuk kantor
        // Kirim toko TIDAK dikurangi karena itu dari stok kantor, bukan dari finishing
        // Tambahkan perhitungan stok kantor = masuk kantor - kirim toko - jual gudang
        $jualGudang = \DB::table('jual_gudang')
            ->select('model')
            ->selectRaw('SUM(pcs) as total_jual')
            ->whereIn('status', ['lunas', 'piutang'])
            ->groupBy('model')
            ->get()
            ->keyBy('model');

        $data = $finishing->map(function ($row) use ($masukKantor, $kirimToko, $jualGudang) {
            $totalJadi  = (int) $row->total_jadi;
            $totalMasuk = (int) ($masukKantor->get($row->model)?->total_masuk ?? 0);
            $totalKirim = (int) ($kirimToko->get($row->model)?->total_kirim ?? 0);
            $totalJual  = (int) ($jualGudang->get($row->model)?->total_jual ?? 0);
            
            // Siap kirim = finishing - yang sudah masuk kantor
            $siapKirim = $totalJadi - $totalMasuk;
            
            // Stok kantor = masuk kantor - kirim toko - jual gudang
            $stokKantor = $totalMasuk - $totalKirim - $totalJual;

            return [
                'model'         => $row->model,
                'total_jadi'    => $totalJadi,
                'ke_kantor'     => max(0, $stokKantor), // Stok yang tersedia di kantor
                'ke_toko'       => $totalKirim,
                'siap_kirim'    => max(0, $siapKirim),
            ];
        })
        ->filter(fn ($r) => $r['total_jadi'] > 0)
        ->sortBy('model')
        ->values();

        $summary = [
            'total_model'      => $data->count(),
            'total_siap_kirim' => $data->sum('siap_kirim'),
            'total_ke_kantor'  => $data->sum('ke_kantor'),
            'total_ke_toko'    => $data->sum('ke_toko'),
            'total_jadi'       => $data->sum('total_jadi'),
        ];

        return Inertia::render('BarangSiapKirim/Index', [
            'barangSiapKirim' => $data,
            'summary'         => $summary,
        ]);
    }
}
