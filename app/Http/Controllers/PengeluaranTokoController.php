<?php

namespace App\Http\Controllers;

use App\Models\PengeluaranToko;
use App\Models\KasToko;
use App\Models\Toko;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        DB::transaction(function () use ($validated, $user) {
            $pengeluaran = PengeluaranToko::create($validated);

            // Kurangi saldo kas toko
            $toko = Toko::lockForUpdate()->findOrFail($validated['toko_id']);
            $metode = $validated['metode_bayar'];
            $saldoField = 'saldo_' . $metode;
            
            $saldoSebelum = $toko->$saldoField;
            $saldoSesudah = $saldoSebelum - $validated['jumlah'];
            
            // Validasi saldo tidak boleh negatif
            if ($saldoSesudah < 0) {
                throw new \Exception('Saldo ' . $metode . ' tidak mencukupi');
            }
            
            // Buat transaksi kas keluar
            KasToko::create([
                'toko_id' => $validated['toko_id'],
                'tanggal' => $validated['tanggal'],
                'jenis' => 'keluar',
                'metode_bayar' => $metode,
                'kategori' => $validated['kategori'],
                'jumlah' => $validated['jumlah'],
                'keterangan' => $validated['keterangan'],
                'referensi' => 'PENGELUARAN-' . $pengeluaran->id,
                'saldo_sebelum' => $saldoSebelum,
                'saldo_sesudah' => $saldoSesudah,
                'user_id' => $user->id,
            ]);
            
            // Update saldo toko
            $toko->update([
                $saldoField => $saldoSesudah,
                'saldo_kas' => $toko->saldo_cash + $toko->saldo_transfer + $toko->saldo_debit - $validated['jumlah']
            ]);
        });

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

        DB::transaction(function () use ($pengeluaranToko, $validated) {
            $oldJumlah = $pengeluaranToko->jumlah;
            $oldMetode = $pengeluaranToko->metode_bayar;
            
            // Kembalikan saldo lama
            $toko = Toko::lockForUpdate()->findOrFail($pengeluaranToko->toko_id);
            $oldSaldoField = 'saldo_' . $oldMetode;
            $toko->$oldSaldoField += $oldJumlah;
            $toko->saldo_kas += $oldJumlah;
            
            // Kurangi saldo baru
            $newMetode = $validated['metode_bayar'];
            $newSaldoField = 'saldo_' . $newMetode;
            
            if ($toko->$newSaldoField < $validated['jumlah']) {
                throw new \Exception('Saldo ' . $newMetode . ' tidak mencukupi');
            }
            
            $toko->$newSaldoField -= $validated['jumlah'];
            $toko->saldo_kas -= $validated['jumlah'];
            $toko->save();
            
            // Update pengeluaran
            $pengeluaranToko->update($validated);
            
            // Update transaksi kas
            $kasToko = KasToko::where('referensi', 'PENGELUARAN-' . $pengeluaranToko->id)->first();
            if ($kasToko) {
                $kasToko->update([
                    'tanggal' => $validated['tanggal'],
                    'metode_bayar' => $newMetode,
                    'kategori' => $validated['kategori'],
                    'jumlah' => $validated['jumlah'],
                    'keterangan' => $validated['keterangan'],
                    'saldo_sebelum' => $toko->$newSaldoField + $validated['jumlah'],
                    'saldo_sesudah' => $toko->$newSaldoField,
                ]);
            }
        });

        return redirect()->back()->with('success', 'Pengeluaran berhasil diupdate');
    }

    public function destroy(PengeluaranToko $pengeluaranToko)
    {
        $user = auth()->user();

        // Validasi akses
        if ($user->isToko() && $pengeluaranToko->toko_id !== $user->toko_id) {
            abort(403, 'Unauthorized');
        }

        DB::transaction(function () use ($pengeluaranToko) {
            // Kembalikan saldo kas
            $toko = Toko::lockForUpdate()->findOrFail($pengeluaranToko->toko_id);
            $metode = $pengeluaranToko->metode_bayar;
            $saldoField = 'saldo_' . $metode;
            
            $toko->$saldoField += $pengeluaranToko->jumlah;
            $toko->saldo_kas += $pengeluaranToko->jumlah;
            $toko->save();
            
            // Hapus transaksi kas terkait
            KasToko::where('referensi', 'PENGELUARAN-' . $pengeluaranToko->id)->delete();
            
            $pengeluaranToko->delete();
        });

        return redirect()->back()->with('success', 'Pengeluaran berhasil dihapus');
    }
}
