<?php

namespace App\Http\Controllers;

use App\Models\PengeluaranToko;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PengeluaranTokoController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $tokoId = $user->isToko() ? $user->toko_id : null;

        $query = PengeluaranToko::with('toko')
            ->when($tokoId, fn($q) => $q->where('toko_id', $tokoId))
            ->when($request->search, function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('kategori', 'like', "%{$search}%")
                      ->orWhere('keterangan', 'like', "%{$search}%");
                });
            })
            ->latest('tanggal')
            ->latest('id');

        $data = $query->paginate(15)->withQueryString();

        // Summary
        $totalPengeluaran = PengeluaranToko::when($tokoId, fn($q) => $q->where('toko_id', $tokoId))
            ->sum('jumlah');

        $pengeluaranBulanIni = PengeluaranToko::when($tokoId, fn($q) => $q->where('toko_id', $tokoId))
            ->whereMonth('tanggal', now()->month)
            ->whereYear('tanggal', now()->year)
            ->sum('jumlah');

        $pengeluaranPerKategori = PengeluaranToko::when($tokoId, fn($q) => $q->where('toko_id', $tokoId))
            ->selectRaw('kategori, SUM(jumlah) as total')
            ->groupBy('kategori')
            ->get()
            ->pluck('total', 'kategori');

        return Inertia::render('PengeluaranToko/Index', [
            'data' => $data,
            'totalPengeluaran' => (float) $totalPengeluaran,
            'pengeluaranBulanIni' => (float) $pengeluaranBulanIni,
            'pengeluaranPerKategori' => $pengeluaranPerKategori,
            'isAdmin' => $user->isAdmin(),
            'userTokoId' => $tokoId,
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        
        $validated = $request->validate([
            'toko_id' => $user->isAdmin() ? 'required|exists:tokos,id' : 'nullable',
            'tanggal' => 'required|date',
            'kategori' => 'required|string|max:255',
            'keterangan' => 'required|string|max:500',
            'jumlah' => 'required|numeric|min:0',
            'metode_bayar' => 'required|in:cash,transfer,debit',
        ]);

        // Jika user toko, gunakan toko_id mereka
        if ($user->isToko()) {
            $validated['toko_id'] = $user->toko_id;
        }

        PengeluaranToko::create($validated);

        return redirect()->back()->with('success', 'Pengeluaran berhasil ditambahkan');
    }

    public function update(Request $request, PengeluaranToko $pengeluaranToko)
    {
        $user = auth()->user();

        // Validasi akses
        if ($user->isToko() && $pengeluaranToko->toko_id !== $user->toko_id) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'tanggal' => 'required|date',
            'kategori' => 'required|string|max:255',
            'keterangan' => 'required|string|max:500',
            'jumlah' => 'required|numeric|min:0',
            'metode_bayar' => 'required|in:cash,transfer,debit',
        ]);

        $pengeluaranToko->update($validated);

        return redirect()->back()->with('success', 'Pengeluaran berhasil diupdate');
    }

    public function destroy(PengeluaranToko $pengeluaranToko)
    {
        $user = auth()->user();

        // Validasi akses
        if ($user->isToko() && $pengeluaranToko->toko_id !== $user->toko_id) {
            abort(403, 'Unauthorized');
        }

        $pengeluaranToko->delete();

        return redirect()->back()->with('success', 'Pengeluaran berhasil dihapus');
    }
}
