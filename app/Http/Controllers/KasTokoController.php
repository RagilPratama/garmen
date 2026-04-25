<?php

namespace App\Http\Controllers;

use App\Models\KasToko;
use App\Models\KasTransfer;
use App\Models\Toko;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class KasTokoController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $isAdmin = $user->isAdmin();
        
        $query = KasToko::with(['toko', 'user'])
            ->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc');

        // Filter berdasarkan role
        if (!$isAdmin) {
            $query->where('toko_id', $user->toko_id);
        }

        // Filter toko (untuk admin)
        if ($request->toko_id) {
            $query->where('toko_id', $request->toko_id);
        }

        // Filter tanggal
        if ($request->tanggal_dari) {
            $query->whereDate('tanggal', '>=', $request->tanggal_dari);
        }
        if ($request->tanggal_sampai) {
            $query->whereDate('tanggal', '<=', $request->tanggal_sampai);
        }

        // Filter jenis
        if ($request->jenis) {
            $query->where('jenis', $request->jenis);
        }

        $kasData = $query->paginate(50);

        // Get saldo kas per toko
        $saldoKas = [];
        if ($isAdmin) {
            $tokos = Toko::where('is_active', true)->get();
            foreach ($tokos as $toko) {
                $saldoKas[$toko->id] = [
                    'nama_toko' => $toko->nama_toko,
                    'saldo' => $toko->saldo_kas,
                    'saldo_cash' => $toko->saldo_cash,
                    'saldo_transfer' => $toko->saldo_transfer,
                    'saldo_debit' => $toko->saldo_debit,
                ];
            }
        } else {
            $toko = $user->toko;
            $saldoKas[$toko->id] = [
                'nama_toko' => $toko->nama_toko,
                'saldo' => $toko->saldo_kas,
                'saldo_cash' => $toko->saldo_cash,
                'saldo_transfer' => $toko->saldo_transfer,
                'saldo_debit' => $toko->saldo_debit,
            ];
        }

        return Inertia::render('KasToko/Index', [
            'kasData' => $kasData,
            'saldoKas' => $saldoKas,
            'tokos' => $isAdmin ? Toko::where('is_active', true)->get() : collect([$user->toko]),
            'semuaTokos' => Toko::where('is_active', true)->get(),
            'filters' => $request->only(['toko_id', 'tanggal_dari', 'tanggal_sampai', 'jenis']),
            'isAdmin' => $isAdmin,
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $isAdmin = $user->isAdmin();

        $validated = $request->validate([
            'toko_id' => 'required|exists:tokos,id',
            'tanggal' => 'required|date',
            'jenis' => 'required|in:masuk,keluar',
            'metode_bayar' => 'required|in:cash,transfer,debit',
            'kategori' => 'required|string|max:255',
            'jumlah' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string',
        ]);

        // Validasi akses toko
        if (!$isAdmin && $validated['toko_id'] != $user->toko_id) {
            return back()->withErrors(['toko_id' => 'Anda tidak memiliki akses ke toko ini']);
        }

        DB::transaction(function () use ($validated, $user) {
            $toko = Toko::lockForUpdate()->findOrFail($validated['toko_id']);
            
            $metode = $validated['metode_bayar'];
            $saldoField = 'saldo_' . $metode; // saldo_cash, saldo_transfer, saldo_debit
            
            $saldoSebelum = $toko->$saldoField;
            $jumlah = $validated['jumlah'];
            
            // Hitung saldo baru
            if ($validated['jenis'] === 'masuk') {
                $saldoSesudah = $saldoSebelum + $jumlah;
            } else {
                $saldoSesudah = $saldoSebelum - $jumlah;
                
                // Validasi saldo tidak boleh negatif
                if ($saldoSesudah < 0) {
                    throw new \Exception('Saldo ' . $metode . ' tidak mencukupi');
                }
            }

            // Simpan transaksi kas
            KasToko::create([
                'toko_id' => $validated['toko_id'],
                'tanggal' => $validated['tanggal'],
                'jenis' => $validated['jenis'],
                'metode_bayar' => $metode,
                'kategori' => $validated['kategori'],
                'jumlah' => $jumlah,
                'keterangan' => $validated['keterangan'],
                'saldo_sebelum' => $saldoSebelum,
                'saldo_sesudah' => $saldoSesudah,
                'user_id' => $user->id,
            ]);

            // Update saldo toko per metode
            $toko->update([
                $saldoField => $saldoSesudah,
                'saldo_kas' => $toko->saldo_cash + $toko->saldo_transfer + $toko->saldo_debit + ($saldoSesudah - $saldoSebelum)
            ]);
        });

        return redirect()->route('kas-toko.index')->with('success', 'Transaksi kas berhasil ditambahkan');
    }

    public function destroy(KasToko $kasToko)
    {
        $user = auth()->user();
        $isAdmin = $user->isAdmin();

        // Validasi akses
        if (!$isAdmin && $kasToko->toko_id != $user->toko_id) {
            return back()->withErrors(['error' => 'Anda tidak memiliki akses untuk menghapus transaksi ini']);
        }

        DB::transaction(function () use ($kasToko) {
            $toko = Toko::lockForUpdate()->findOrFail($kasToko->toko_id);
            
            $metode = $kasToko->metode_bayar;
            $saldoField = 'saldo_' . $metode;
            
            // Kembalikan saldo
            if ($kasToko->jenis === 'masuk') {
                $toko->$saldoField -= $kasToko->jumlah;
                $toko->saldo_kas -= $kasToko->jumlah;
            } else {
                $toko->$saldoField += $kasToko->jumlah;
                $toko->saldo_kas += $kasToko->jumlah;
            }
            
            $toko->save();
            $kasToko->delete();
        });

        return redirect()->route('kas-toko.index')->with('success', 'Transaksi kas berhasil dihapus');
    }

    public function laporan(Request $request)
    {
        $user = auth()->user();
        $isAdmin = $user->isAdmin();

        $bulan = $request->bulan ?? now()->month;
        $tahun = $request->tahun ?? now()->year;
        $tokoId = $request->toko_id;

        // Filter berdasarkan role
        if (!$isAdmin) {
            $tokoId = $user->toko_id;
        }

        $query = KasToko::with(['toko', 'user'])
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun);

        if ($tokoId) {
            $query->where('toko_id', $tokoId);
        }

        $transaksi = $query->orderBy('tanggal')->orderBy('created_at')->get();

        // Ringkasan
        $totalMasuk = $transaksi->where('jenis', 'masuk')->sum('jumlah');
        $totalKeluar = $transaksi->where('jenis', 'keluar')->sum('jumlah');
        $saldoAkhir = $totalMasuk - $totalKeluar;

        // Grouping by kategori
        $perKategori = $transaksi->groupBy('kategori')->map(function ($items, $kategori) {
            return [
                'kategori' => $kategori,
                'total' => $items->sum('jumlah'),
                'count' => $items->count(),
            ];
        })->values();

        return Inertia::render('KasToko/Laporan', [
            'transaksi' => $transaksi,
            'totalMasuk' => $totalMasuk,
            'totalKeluar' => $totalKeluar,
            'saldoAkhir' => $saldoAkhir,
            'perKategori' => $perKategori,
            'tokos' => $isAdmin ? Toko::where('is_active', true)->get() : collect([$user->toko]),
            'filters' => [
                'bulan' => $bulan,
                'tahun' => $tahun,
                'toko_id' => $tokoId,
            ],
            'isAdmin' => $isAdmin,
        ]);
    }

    public function transfer(Request $request)
    {
        $user = auth()->user();
        $isAdmin = $user->isAdmin();

        $validated = $request->validate([
            'toko_pengirim_id' => 'required|exists:tokos,id',
            'toko_penerima_id' => 'required|exists:tokos,id|different:toko_pengirim_id',
            'tanggal' => 'required|date',
            'metode_bayar' => 'required|in:cash,transfer,debit',
            'jumlah' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string',
        ]);

        // Validasi akses
        if (!$isAdmin) {
            if ($validated['toko_pengirim_id'] != $user->toko_id) {
                return back()->withErrors(['toko_pengirim_id' => 'Anda hanya bisa transfer dari toko Anda sendiri']);
            }
            if ($validated['toko_penerima_id'] == $user->toko_id) {
                return back()->withErrors(['toko_penerima_id' => 'Toko penerima tidak boleh sama dengan toko Anda']);
            }
        }

        DB::transaction(function () use ($validated, $user) {
            $tokoPengirim = Toko::lockForUpdate()->findOrFail($validated['toko_pengirim_id']);
            $tokoPenerima = Toko::lockForUpdate()->findOrFail($validated['toko_penerima_id']);

            $metode = $validated['metode_bayar'];
            $saldoField = 'saldo_' . $metode;
            $jumlah = $validated['jumlah'];

            // Cek saldo pengirim
            $saldoSebelumPengirim = $tokoPengirim->$saldoField;
            if ($saldoSebelumPengirim < $jumlah) {
                throw new \Exception('Saldo ' . $metode . ' toko pengirim tidak mencukupi');
            }

            // Update saldo pengirim
            $saldoSesudahPengirim = $saldoSebelumPengirim - $jumlah;
            $tokoPengirim->$saldoField = $saldoSesudahPengirim;
            $tokoPengirim->saldo_kas = $tokoPengirim->saldo_cash + $tokoPengirim->saldo_transfer + $tokoPengirim->saldo_debit;

            // Update saldo penerima
            $saldoSebelumPenerima = $tokoPenerima->$saldoField;
            $saldoSesudahPenerima = $saldoSebelumPenerima + $jumlah;
            $tokoPenerima->$saldoField = $saldoSesudahPenerima;
            $tokoPenerima->saldo_kas = $tokoPenerima->saldo_cash + $tokoPenerima->saldo_transfer + $tokoPenerima->saldo_debit;

            // Save perubahan saldo toko
            $tokoPengirim->save();
            $tokoPenerima->save();

            // Record di kas_toko untuk pengirim (keluar)
            KasToko::create([
                'toko_id' => $validated['toko_pengirim_id'],
                'tanggal' => $validated['tanggal'],
                'jenis' => 'keluar',
                'metode_bayar' => $metode,
                'kategori' => 'Transfer ke ' . $tokoPenerima->nama_toko,
                'jumlah' => $jumlah,
                'keterangan' => $validated['keterangan'] ?? 'Transfer ke ' . $tokoPenerima->nama_toko,
                'saldo_sebelum' => $saldoSebelumPengirim,
                'saldo_sesudah' => $saldoSesudahPengirim,
                'user_id' => $user->id,
            ]);

            // Record di kas_toko untuk penerima (masuk)
            KasToko::create([
                'toko_id' => $validated['toko_penerima_id'],
                'tanggal' => $validated['tanggal'],
                'jenis' => 'masuk',
                'metode_bayar' => $metode,
                'kategori' => 'Transfer dari ' . $tokoPengirim->nama_toko,
                'jumlah' => $jumlah,
                'keterangan' => 'Transfer dari ' . $tokoPengirim->nama_toko,
                'saldo_sebelum' => $saldoSebelumPenerima,
                'saldo_sesudah' => $saldoSesudahPenerima,
                'user_id' => $user->id,
            ]);

            // Record di kas_transfer
            KasTransfer::create([
                'toko_pengirim_id' => $validated['toko_pengirim_id'],
                'toko_penerima_id' => $validated['toko_penerima_id'],
                'tanggal' => $validated['tanggal'],
                'metode_bayar' => $metode,
                'jumlah' => $jumlah,
                'keterangan' => $validated['keterangan'] ?? null,
                'user_id' => $user->id,
            ]);
        });

        return redirect()->route('kas-toko.index')->with('success', 'Transfer kas berhasil');
    }
}
