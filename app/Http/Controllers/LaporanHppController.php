<?php

namespace App\Http\Controllers;

use App\Models\BahanProsesPotong;
use App\Models\ProsesFinishing;
use App\Models\BahanKeluar;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class LaporanHppController extends Controller
{
    public function index()
    {
        $search = request('search');

        // Kita ambil data dari BahanProsesPotong sebagai anchor (setiap PO mulai dari sini)
        $query = BahanProsesPotong::select(
                'bahan_proses_potong.po',
                'bahan_proses_potong.model',
                'bahan_proses_potong.kode_bahan',
                'bahan_proses_potong.yard as yard_pakai',
                'proses_finishing.pcs_barang_jadi',
                'proses_finishing.harga_satuan as ongkos_produksi'
            )
            ->leftJoin('proses_finishing', 'bahan_proses_potong.po', '=', 'proses_finishing.po')
            ->when($search, function($q) use ($search) {
                $q->where('bahan_proses_potong.po', 'ilike', "%$search%")
                  ->orWhere('bahan_proses_potong.model', 'ilike', "%$search%");
            });

        $results = $query->get()->map(function($item) {
            // Cari harga bahan per yard dari tabel BahanKeluar (ambil rata-rata atau terbaru)
            // Di sistem ini kita asumsikan harga bahan diambil dari BahanKeluar yang sesuai kode_bahan
            $hargaBahan = BahanKeluar::where('kode_bahan', $item->kode_bahan)
                ->orderBy('tanggal', 'desc')
                ->first();
            
            $rpPerYard = $hargaBahan ? (float) $hargaBahan->rp_per_yard : 0;
            
            $totalBiayaBahan = $item->yard_pakai * $rpPerYard;
            $totalOngkosProduksi = ($item->pcs_barang_jadi ?? 0) * (float) ($item->ongkos_produksi ?? 0);
            
            $totalModal = $totalBiayaBahan + $totalOngkosProduksi;
            $hppPerPcs = $item->pcs_barang_jadi > 0 ? $totalModal / $item->pcs_barang_jadi : 0;

            return [
                'po' => $item->po,
                'model' => $item->model,
                'kode_bahan' => $item->kode_bahan,
                'yard_pakai' => (float) $item->yard_pakai,
                'rp_per_yard' => $rpPerYard,
                'total_biaya_bahan' => $totalBiayaBahan,
                'pcs_jadi' => (int) $item->pcs_barang_jadi,
                'ongkos_produksi_satuan' => (float) $item->ongkos_produksi,
                'total_ongkos_produksi' => $totalOngkosProduksi,
                'total_modal_po' => $totalModal,
                'hpp_satuan' => $hppPerPcs
            ];
        });

        return Inertia::render('LaporanHpp/Index', [
            'data' => $results,
            'filters' => ['search' => $search]
        ]);
    }
}
