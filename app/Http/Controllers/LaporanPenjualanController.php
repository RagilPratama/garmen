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
    /**
     * Helper: build the full combined report collection (without pagination).
     */
    private function buildReport(?string $search = null, ?string $startDate = null, ?string $endDate = null)
    {
        $gudang = JualGudang::select('no_nota', 'tanggal_nota', 'buyer', 'status', 'total_harga', 'model', 'pcs', 'harga_satuan', 'diskon', DB::raw("'gudang' as tipe"))
            ->when($search, fn($q) => $q->where('no_nota', 'ilike', "%$search%")->orWhere('buyer', 'ilike', "%$search%"))
            ->when($startDate, fn($q) => $q->whereDate('tanggal_nota', '>=', $startDate))
            ->when($endDate,   fn($q) => $q->whereDate('tanggal_nota', '<=', $endDate))
            ->get();

        $toko = ProsesJual::select('no_nota', 'tanggal_nota', 'buyer', 'status', 'total_harga', 'model', 'pcs', 'harga_satuan', 'diskon', DB::raw("'toko' as tipe"))
            ->when($search, fn($q) => $q->where('no_nota', 'ilike', "%$search%")->orWhere('buyer', 'ilike', "%$search%"))
            ->when($startDate, fn($q) => $q->whereDate('tanggal_nota', '>=', $startDate))
            ->when($endDate,   fn($q) => $q->whereDate('tanggal_nota', '<=', $endDate))
            ->get();

        $combined = $gudang->concat($toko);

        $pembayaran = PenjualanPembayaran::select('no_nota', 'channel', DB::raw('SUM(jumlah) as total_bayar'))
            ->groupBy('no_nota', 'channel')
            ->get()
            ->keyBy(fn($item) => $item->no_nota . '-' . $item->channel);

        return $combined->groupBy(fn($item) => $item->no_nota . '-' . $item->tipe)
            ->map(function ($group) use ($pembayaran) {
                $first      = $group->first();
                $nota       = $first->no_nota;
                $tipe       = $first->tipe;
                $totalHarga = $group->sum('total_harga');
                $channel    = $tipe === 'gudang' ? 'gudang' : 'toko';
                $totalBayar = $pembayaran->get($nota . '-' . $channel)?->total_bayar ?? 0;

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
                    'total_bayar'  => (float) $totalBayar,
                    'sisa_piutang' => max(0, $totalHarga - $totalBayar),
                    'tipe'         => $tipe,
                    'models'       => $models,
                ];
            })
            ->sortByDesc('tanggal_nota')
            ->values();
    }

    public function index()
    {
        $search    = request('search');
        $startDate = request('start_date');
        $endDate   = request('end_date');

        $report = $this->buildReport($search, $startDate, $endDate);

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

    /**
     * Return all report rows as JSON (for client-side PDF generation).
     */
    public function exportData()
    {
        $search    = request('search');
        $startDate = request('start_date');
        $endDate   = request('end_date');

        $report = $this->buildReport($search, $startDate, $endDate);

        return response()->json($report);
    }
}
