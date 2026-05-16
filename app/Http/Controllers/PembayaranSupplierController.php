<?php

namespace App\Http\Controllers;

use App\Models\BahanMasukPembayaran;
use App\Models\SuratJalanMasuk;
use App\Models\BarcodeBahan;
use App\Models\Rekening;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PembayaranSupplierController extends Controller
{
    public function index()
    {
        $search = request('search');
        $status = request('status');

        // Ambil semua surat jalan masuk yang punya nota
        $query = SuratJalanMasuk::whereNotNull('no_nota')
            ->when($search, fn($q) => $q->where(fn($q) => $q
                ->where('no_nota', 'like', "%{$search}%")
                ->orWhere('no_surat_jalan', 'like', "%{$search}%")
            ))
            ->orderByDesc('tanggal')
            ->paginate(20)
            ->appends(request()->query());

        // Transform: hitung total tagihan, sudah bayar, sisa
        $query->getCollection()->transform(function ($sj) {
            // Total tagihan dari barcode yang terhubung
            $barcodes = BarcodeBahan::where('no_surat_jalan', $sj->no_surat_jalan)
                ->where('harga_sudah_diisi', true)
                ->get();
            $sj->total_tagihan = (float) $barcodes->sum('total_harga');
            $sj->jumlah_item = $barcodes->count();

            // Total sudah dibayar
            $pembayarans = BahanMasukPembayaran::where('no_nota', $sj->no_nota)->get();
            $sj->total_dibayar = (float) $pembayarans->sum('jumlah');
            $sj->sisa_tagihan = max(0, $sj->total_tagihan - $sj->total_dibayar);
            $sj->status_bayar = $sj->total_tagihan <= 0 ? 'belum_ada' : ($sj->sisa_tagihan <= 0 ? 'lunas' : 'belum_lunas');
            $sj->pembayarans = $pembayarans;

            return $sj;
        });

        // Filter status
        if ($status === 'lunas') {
            $filtered = $query->getCollection()->filter(fn($sj) => $sj->status_bayar === 'lunas');
            $query->setCollection($filtered->values());
        } elseif ($status === 'belum_lunas') {
            $filtered = $query->getCollection()->filter(fn($sj) => $sj->status_bayar === 'belum_lunas');
            $query->setCollection($filtered->values());
        }

        return Inertia::render('PembayaranSupplier/Index', [
            'data' => $query,
            'rekeningOptions' => Rekening::orderBy('bank')->get(['id', 'bank', 'nama', 'nomor_rekening']),
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'no_nota' => 'required|string|max:100',
            'tanggal_bayar' => 'required|date',
            'jumlah' => 'required|numeric|min:1',
            'metode' => 'required|string|in:cash,transfer',
            'rekening_id' => 'nullable|exists:rekening,id',
            'keterangan' => 'nullable|string|max:255',
        ]);

        BahanMasukPembayaran::create($request->only(['no_nota', 'tanggal_bayar', 'jumlah', 'metode', 'rekening_id', 'keterangan']));

        return redirect()->route('pembayaran-supplier.index')->with('message', 'Pembayaran berhasil ditambahkan.');
    }

    public function destroy($id)
    {
        BahanMasukPembayaran::findOrFail($id)->delete();
        return redirect()->route('pembayaran-supplier.index')->with('message', 'Pembayaran berhasil dihapus.');
    }
}
