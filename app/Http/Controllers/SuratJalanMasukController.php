<?php

namespace App\Http\Controllers;

use App\Models\SuratJalanMasuk;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SuratJalanMasukController extends Controller
{
    public function index()
    {
        $search = request('search');

        $data = SuratJalanMasuk::when($search, fn($q) => $q->where('no_surat_jalan', 'like', "%{$search}%"))
            ->orderByDesc('tanggal')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->appends(request()->query());

        // Hitung jumlah barcode yang terhubung per surat jalan
        $data->getCollection()->transform(function ($item) {
            $barcodes = \App\Models\BarcodeBahan::where('no_surat_jalan', $item->no_surat_jalan)
                ->where('harga_sudah_diisi', true)
                ->get();
            $item->jumlah_item = $barcodes->count();
            $item->total_qty = (float) $barcodes->sum('quantity');
            $item->total_harga = (float) $barcodes->sum('total_harga');
            return $item;
        });

        return Inertia::render('SuratJalanMasuk/Index', [
            'data' => $data,
            'nextSuratJalan' => $this->generateNextNumber(),
        ]);
    }

    public function create()
    {
        return Inertia::render('SuratJalanMasuk/Create', [
            'nextSuratJalan' => $this->generateNextNumber(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'no_surat_jalan' => 'required|string|max:100|unique:surat_jalan_masuk,no_surat_jalan',
            'tanggal' => 'required|date',
            'keterangan' => 'nullable|string|max:255',
        ]);

        SuratJalanMasuk::create([
            'no_surat_jalan' => $request->no_surat_jalan,
            'tanggal' => $request->tanggal,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->route('surat-jalan-masuk.index')->with('message', 'Surat jalan masuk berhasil dibuat.');
    }

    public function destroy($id)
    {
        SuratJalanMasuk::findOrFail($id)->delete();
        return redirect()->route('surat-jalan-masuk.index')->with('message', 'Surat jalan masuk berhasil dihapus.');
    }

    public function detail()
    {
        $noSuratJalan = request('no_surat_jalan');

        $items = \App\Models\BarcodeBahan::where('no_surat_jalan', $noSuratJalan)
            ->where('harga_sudah_diisi', true)
            ->orderBy('kode_bahan')
            ->get();

        return response()->json($items);
    }

    private function generateNextNumber(): string
    {
        $latest = SuratJalanMasuk::orderByDesc('id')->first();

        if (!$latest) {
            return 'SJ-MSK-0001';
        }

        preg_match('/(\d+)$/', $latest->no_surat_jalan, $matches);
        $nextNum = isset($matches[1]) ? (int) $matches[1] + 1 : 1;

        return 'SJ-MSK-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
    }
}
