<?php

namespace App\Http\Controllers;

use App\Models\JualGudang;
use App\Models\ProsesJual;
use App\Models\PenjualanPembayaran;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class LaporanPenjualanController extends Controller
{
    public function index()
    {
        $search = request('search');
        
        // Fetch data from Gudang
        $gudang = JualGudang::select('no_nota', 'tanggal_nota', 'buyer', 'status', 'total_harga', 'model', 'pcs', 'harga_satuan', 'diskon', DB::raw("'gudang' as tipe"))
            ->when($search, function ($q) use ($search) {
                $q->where('no_nota', 'ilike', "%$search%")
                  ->orWhere('buyer', 'ilike', "%$search%");
            })
            ->get();

        // Fetch data from Toko
        $toko = ProsesJual::select('no_nota', 'tanggal_nota', 'buyer', 'status', 'total_harga', 'model', 'pcs', 'harga_satuan', 'diskon', DB::raw("'toko' as tipe"))
            ->when($search, function ($q) use ($search) {
                $q->where('no_nota', 'ilike', "%$search%")
                  ->orWhere('buyer', 'ilike', "%$search%");
            })
            ->get();

        // Combine
        $combined = $gudang->concat($toko);
        
        $pembayaran = PenjualanPembayaran::select('no_nota', 'channel', DB::raw('SUM(jumlah) as total_bayar'))
            ->groupBy('no_nota', 'channel')
            ->get()
            ->keyBy(function($item) {
                return $item->no_nota . '-' . $item->channel;
            });

        $report = $combined->groupBy(function($item) {
            return $item->no_nota . '-' . $item->tipe;
        })->map(function ($group) use ($pembayaran) {
            $first = $group->first();
            $nota = $first->no_nota;
            $tipe = $first->tipe;
            $totalHarga = $group->sum('total_harga');
            $channel = $tipe === 'gudang' ? 'gudang' : 'toko';
            $totalBayar = $pembayaran->get($nota . '-' . $channel)?->total_bayar ?? 0;
            
            // Models details
            $models = $group->map(fn($r) => [
                'model'        => $r->model,
                'pcs'          => $r->pcs,
                'harga_satuan' => $r->harga_satuan,
                'diskon'       => $r->diskon,
                'total_harga'  => $r->total_harga,
            ])->values();

            return [
                'no_nota'      => $nota,
                'tanggal_nota' => $first->tanggal_nota,
                'buyer'        => $first->buyer,
                'status'       => $first->status,
                'total_harga'  => $totalHarga,
                'total_bayar'  => $totalBayar,
                'sisa_piutang' => max(0, $totalHarga - $totalBayar),
                'tipe'         => $tipe,
                'models'       => $models,
            ];
        })->sortByDesc('tanggal_nota')->values();

        $page    = (int) request('page', 1);
        $perPage = 15;
        $total   = $report->count();
        $items   = $report->slice(($page - 1) * $perPage, $perPage)->values();
        
        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $items, $total, $perPage, $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return Inertia::render('LaporanPenjualan/Index', [
            'data' => $paginated,
        ]);
    }
}
