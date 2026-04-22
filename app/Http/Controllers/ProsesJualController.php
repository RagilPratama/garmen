<?php
namespace App\Http\Controllers;
use App\Models\ProsesJual;
use App\Models\BarangKirimToko;
use App\Models\PenjualanPembayaran;
use App\Models\Rekening;
use App\Models\KasToko;
use App\Models\Toko;
use App\Traits\GeneratesSuratJalan;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class ProsesJualController extends Controller
{
    use GeneratesSuratJalan;

    public function index()
    {
        $user = auth()->user();
        $search  = request('search');
        
        // Filter berdasarkan role user
        $query = ProsesJual::with('toko')->latest();
        
        if ($user->isTokoJomei() || $user->isTokoKamiko()) {
            // User toko hanya lihat data toko mereka
            $query->where('toko_id', $user->toko_id);
        }
        
        $allRows = $query->when($search, fn($q) => $q->where(fn($q) => $q
                ->where('buyer', 'like', "%{$search}%")
                ->orWhere('model', 'like', "%{$search}%")
                ->orWhere('no_nota', 'like', "%{$search}%")
                ->orWhere('status', 'like', "%{$search}%")
            ))->get();

        // Load pembayaran per no_nota
        $pembayaranMap = PenjualanPembayaran::where('channel', 'toko')
            ->get()
            ->groupBy('no_nota');

        $grouped = $allRows->groupBy('no_nota')->map(function ($rows, $nota) use ($pembayaranMap) {
            $models = $rows->map(fn($r) => [
                'id'           => $r->id,
                'model'        => $r->model,
                'pcs'          => $r->pcs,
                'harga_satuan' => $r->harga_satuan,
                'diskon'       => $r->diskon,
                'total_harga'  => $r->total_harga,
                'status'       => $r->status,
            ])->values();
            $totalHarga  = $rows->sum(fn($r) => (float) $r->total_harga);
            $pembayarans = $pembayaranMap->get($nota, collect())->values();
            $totalBayar  = $pembayarans->sum('jumlah');
            return [
                'no_nota'      => $nota,
                'tanggal_nota' => $rows->min('tanggal_nota'),
                'buyer'        => $rows->first()->buyer,
                'toko'         => $rows->first()->toko?->nama_toko ?? '-',
                'status'       => $rows->first()->status,
                'jumlah_model' => $rows->count(),
                'total_pcs'    => $rows->sum(fn($r) => (int) $r->pcs),
                'total_harga'  => $totalHarga,
                'total_bayar'  => $totalBayar,
                'sisa_piutang' => max(0, $totalHarga - $totalBayar),
                'models'       => $models,
                'pembayarans'  => $pembayarans,
            ];
        })->sortByDesc('tanggal_nota')->values();

        $page    = (int) request('page', 1);
        $perPage = 15;
        $total   = $grouped->count();
        $items   = $grouped->slice(($page - 1) * $perPage, $perPage)->values();
        $data    = new \Illuminate\Pagination\LengthAwarePaginator(
            $items, $total, $perPage, $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        // Stok toko berdasarkan toko_id user
        $tokoId = $user->isToko() ? $user->toko_id : null;
        
        $dikirimQuery = BarangKirimToko::selectRaw('model, SUM(pcs_barang_jadi) as total')
            ->groupBy('model');
        
        if ($tokoId) {
            $dikirimQuery->where('toko_id', $tokoId);
        }
        
        $dikirim = $dikirimQuery->get()->keyBy('model');
        
        $terjualQuery = ProsesJual::selectRaw('model, SUM(pcs) as total')
            ->whereIn('status', ['lunas', 'piutang'])
            ->groupBy('model');
            
        if ($tokoId) {
            $terjualQuery->where('toko_id', $tokoId);
        }
        
        $terjual = $terjualQuery->get()->keyBy('model');

        // Ambil harga_satuan terakhir dari proses_finishing untuk setiap model
        $hargaFinishing = DB::table('proses_finishing')
            ->select('model', DB::raw('MAX(id) as max_id'))
            ->groupBy('model')->pluck('max_id', 'model');
        $hargaPerModel = [];
        if ($hargaFinishing->count()) {
            $hargaRows = DB::table('proses_finishing')
                ->whereIn('id', $hargaFinishing->values())
                ->pluck('harga_satuan', 'model');
            $hargaPerModel = $hargaRows;
        }

        $stokOptions = $dikirim->map(function ($row) use ($terjual, $hargaPerModel) {
            $sisa = (int) $row->total - (int) ($terjual->get($row->model)?->total ?? 0);
            return [
                'model'        => $row->model,
                'sisa_stok'    => $sisa,
                'harga_satuan' => isset($hargaPerModel[$row->model]) ? (int) $hargaPerModel[$row->model] : 0,
            ];
        })->filter(fn($r) => $r['sisa_stok'] > 0)->values();

        return Inertia::render('ProsesJual/Index', [
            'data'            => $data,
            'stokOptions'     => $stokOptions,
            'nextNota'        => $this->nextCode(ProsesJual::class, 'no_nota', 'NT-TOKO-'),
            'rekeningOptions' => Rekening::orderBy('bank')->get(['id', 'bank', 'nama', 'nomor_rekening']),
            'tokos'           => \App\Models\Toko::orderBy('nama_toko')->get(['id', 'nama_toko']),
            'isAdmin'         => $user->isAdmin(),
            'userTokoId'      => $user->toko_id,
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'toko_id'               => $user->isAdmin() ? 'required|exists:tokos,id' : 'nullable',
            'no_nota'               => 'nullable|string|max:100',
            'tanggal_nota'          => 'required|date',
            'buyer'                 => 'required|string|max:200',
            'status'                => 'required|string|in:piutang,lunas,batal',
            'discount'              => 'nullable|numeric|min:0|max:100',
            'models'                => 'required|array|min:1',
            'models.*.model'        => 'required|string|max:200',
            'models.*.pcs'          => 'required|integer|min:1',
            'models.*.harga_satuan' => 'required|numeric|min:0',
            // Payment fields
            'bayar'                 => 'nullable|numeric|min:0',
            'metode'                => 'nullable|string|in:cash,transfer,debit',
            'rekening_id'           => 'nullable|exists:rekening,id',
            'keterangan_pembayaran' => 'nullable|string|max:255',
        ]);
        
        $now  = now();
        $diskonPersen = $request->discount ?? 0;
        
        // Admin bisa pilih toko, user toko otomatis pakai toko mereka
        $tokoId = $user->isAdmin() ? $request->toko_id : $user->toko_id;
        
        $rows = collect($request->models)->map(fn($m) => [
            'toko_id'      => $tokoId,
            'no_nota'      => $request->no_nota,
            'tanggal_nota' => $request->tanggal_nota,
            'buyer'        => $request->buyer,
            'status'       => $request->status,
            'model'        => $m['model'],
            'pcs'          => $m['pcs'],
            'harga_satuan' => $m['harga_satuan'],
            'diskon'       => $diskonPersen,
            'total_harga'  => ($m['pcs'] * $m['harga_satuan']) * (1 - $diskonPersen / 100),
            'bayar'        => $request->bayar ?? 0,
            'created_at'   => $now,
            'updated_at'   => $now,
        ])->toArray();
        
        DB::transaction(function () use ($rows, $request, $tokoId, $user) {
            ProsesJual::insert($rows);

            // Record payment in PenjualanPembayaran
            if ($request->bayar > 0) {
                PenjualanPembayaran::create([
                    'no_nota'       => $request->no_nota,
                    'channel'       => 'toko',
                    'tanggal_bayar' => $request->tanggal_nota,
                    'jumlah'        => $request->bayar,
                    'metode'        => $request->metode ?? 'cash',
                    'rekening_id'   => $request->rekening_id,
                    'keterangan'    => $request->keterangan_pembayaran ?? 'Pembayaran awal saat buat nota',
                ]);
                
                // Tambahkan ke kas toko untuk semua metode pembayaran
                $toko = Toko::lockForUpdate()->findOrFail($tokoId);
                $metode = $request->metode ?? 'cash';
                $saldoField = 'saldo_' . $metode;
                
                $saldoSebelum = $toko->$saldoField;
                $saldoSesudah = $saldoSebelum + $request->bayar;
                
                KasToko::create([
                    'toko_id' => $tokoId,
                    'tanggal' => $request->tanggal_nota,
                    'jenis' => 'masuk',
                    'metode_bayar' => $metode,
                    'kategori' => 'Penjualan',
                    'jumlah' => $request->bayar,
                    'keterangan' => 'Penjualan ' . $request->no_nota . ' - ' . $request->buyer,
                    'referensi' => $request->no_nota,
                    'saldo_sebelum' => $saldoSebelum,
                    'saldo_sesudah' => $saldoSesudah,
                    'user_id' => $user->id,
                ]);
                
                $toko->update([
                    $saldoField => $saldoSesudah,
                    'saldo_kas' => $toko->saldo_cash + $toko->saldo_transfer + $toko->saldo_debit + $request->bayar
                ]);
            }
        });

        return redirect()->route('proses-jual.index')->with('message', 'Data berhasil ditambahkan.');
    }

    public function update(Request $request, ProsesJual $prosesJual)
    {
        $validated = $request->validate([
            'pcs'          => 'required|integer|min:0',
            'harga_satuan' => 'required|numeric|min:0',
        ]);
        $validated['total_harga'] = ($validated['pcs'] * $validated['harga_satuan']) * (1 - $prosesJual->diskon / 100);
        $prosesJual->update($validated);
        return redirect()->route('proses-jual.index')->with('message', 'Data berhasil diperbarui.');
    }

    public function updateNotaStatus(Request $request)
    {
        $request->validate([
            'no_nota' => 'required|string|max:100',
            'status'  => 'required|string|in:piutang,lunas,batal',
        ]);
        ProsesJual::where('no_nota', $request->no_nota)->update(['status' => $request->status]);
        return redirect()->route('proses-jual.index')->with('message', 'Status nota berhasil diperbarui.');
    }

    public function destroy(ProsesJual $prosesJual)
    {
        $prosesJual->delete();
        return redirect()->route('proses-jual.index')->with('message', 'Data berhasil dihapus.');
    }

    public function getStokToko($tokoId)
    {
        // Stok toko berdasarkan toko_id
        $dikirim = BarangKirimToko::selectRaw('model, SUM(pcs_barang_jadi) as total')
            ->where('toko_id', $tokoId)
            ->groupBy('model')
            ->get()
            ->keyBy('model');
        
        $terjual = ProsesJual::selectRaw('model, SUM(pcs) as total')
            ->where('toko_id', $tokoId)
            ->whereIn('status', ['lunas', 'piutang'])
            ->groupBy('model')
            ->get()
            ->keyBy('model');

        // Ambil harga_satuan terakhir dari proses_finishing untuk setiap model
        $hargaFinishing = DB::table('proses_finishing')
            ->select('model', DB::raw('MAX(id) as max_id'))
            ->groupBy('model')->pluck('max_id', 'model');
        $hargaPerModel = [];
        if ($hargaFinishing->count()) {
            $hargaRows = DB::table('proses_finishing')
                ->whereIn('id', $hargaFinishing->values())
                ->pluck('harga_satuan', 'model');
            $hargaPerModel = $hargaRows;
        }

        $stokOptions = $dikirim->map(function ($row) use ($terjual, $hargaPerModel) {
            $sisa = (int) $row->total - (int) ($terjual->get($row->model)?->total ?? 0);
            return [
                'model'        => $row->model,
                'sisa_stok'    => $sisa,
                'harga_satuan' => isset($hargaPerModel[$row->model]) ? (int) $hargaPerModel[$row->model] : 0,
            ];
        })->filter(fn($r) => $r['sisa_stok'] > 0)->values();

        return response()->json($stokOptions);
    }
}