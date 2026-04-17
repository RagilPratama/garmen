<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Rekening;
use App\Models\Supplier;
use App\Models\MasterModel;
use App\Models\BahanMasuk;
use App\Models\BahanKeluar;
use App\Models\StokBahan;
use App\Models\BahanProsesPotong;
use App\Models\ProsesJahit;
use App\Models\ProsesCuci;
use App\Models\ProsesFinishing;
use App\Models\BarangMasukKantor;
use App\Models\BarangKirimToko;
use App\Models\ProsesJual;
use App\Models\JualGudang;
use App\Models\Defect;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    // Harga jual per model (dipakai di finishing & penjualan)
    private array $hargaModel = [
        'Kemeja Batik Premium'  => ['finishing' => 120000, 'jual' => 185000],
        'Polo Shirt Casual'     => ['finishing' =>  65000, 'jual' =>  95000],
        'Jaket Bomber Fleece'   => ['finishing' => 160000, 'jual' => 250000],
        'Kaos Oblong Basic'     => ['finishing' =>  35000, 'jual' =>  55000],
        'Celana Chino Slim'     => ['finishing' =>  80000, 'jual' => 125000],
        'Kemeja Flannel Kotak'  => ['finishing' =>  95000, 'jual' => 148000],
        'Polo Shirt Premium'    => ['finishing' =>  75000, 'jual' => 115000],
    ];

    public function run(): void
    {
        // ════════════════════════════════════════════════════════════════════════
        //  1. USER
        // ════════════════════════════════════════════════════════════════════════
        User::create([
            'name'     => 'Admin',
            'email'    => 'admin@garmen.com',
            'password' => Hash::make('admin123'),
        ]);

        // ════════════════════════════════════════════════════════════════════════
        //  2. REKENING BANK
        // ════════════════════════════════════════════════════════════════════════
        $rekeningIds = [];
        $rekeningData = [
            ['BCA',     'Garmen Jaya Textile', '1234567890'],
            ['Mandiri', 'Garmen Jaya Textile', '9876543210'],
            ['BNI',     'Garmen Jaya Textile', '1122334455'],
        ];
        foreach ($rekeningData as [$bank, $nama, $nomor]) {
            $rek = Rekening::create(['bank' => $bank, 'nama' => $nama, 'nomor_rekening' => $nomor]);
            $rekeningIds[] = $rek->id;
        }
        [$rekBca, $rekMandiri, $rekBni] = $rekeningIds;

        // ════════════════════════════════════════════════════════════════════════
        //  3. MASTER MODEL
        // ════════════════════════════════════════════════════════════════════════
        $masterModels = [
            ['nama_model' => 'Kemeja Batik Premium',  'keterangan' => 'Kemeja premium motif batik tulis, bahan katun halus'],
            ['nama_model' => 'Polo Shirt Casual',     'keterangan' => 'Polo shirt harian berbahan pique cotton'],
            ['nama_model' => 'Jaket Bomber Fleece',   'keterangan' => 'Jaket bomber dengan bahan fleece premium'],
            ['nama_model' => 'Kaos Oblong Basic',     'keterangan' => 'Kaos oblong basic cotton combed 30s'],
            ['nama_model' => 'Celana Chino Slim',     'keterangan' => 'Celana chino slim fit twill peach'],
            ['nama_model' => 'Kemeja Flannel Kotak',  'keterangan' => 'Kemeja flannel motif kotak casual'],
            ['nama_model' => 'Polo Shirt Premium',    'keterangan' => 'Polo shirt premium double pique'],
        ];
        foreach ($masterModels as $m) {
            MasterModel::create($m);
        }

        // ════════════════════════════════════════════════════════════════════════
        //  4. SUPPLIER
        // ════════════════════════════════════════════════════════════════════════
        Supplier::insert([
            ['nama' => 'Tekstil Nusantara', 'telepon' => '021-5551001', 'alamat' => 'Jl. Tekstil No. 12, Jakarta Barat',  'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Kain Sejahtera',    'telepon' => '022-5552002', 'alamat' => 'Jl. Kain Indah No. 5, Bandung',      'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Bahan Jaya Textil', 'telepon' => '031-5553003', 'alamat' => 'Jl. Raya Industri No. 88, Surabaya', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // ════════════════════════════════════════════════════════════════════════
        //  5. BAHAN MASUK  (8 faktur)
        //     Format: [kode, yard, rp_per_yard, tanggal, supplier, no_sj, no_nota]
        // ════════════════════════════════════════════════════════════════════════
        $bahanMasukRows = [
            ['BHN-001', 500, 12000, '2025-01-05', 'Tekstil Nusantara', 'SJ-IN-001', 'NTA-2025-001'],
            ['BHN-001', 300, 12500, '2025-03-10', 'Tekstil Nusantara', 'SJ-IN-002', 'NTA-2025-002'],
            ['BHN-002', 400, 18000, '2025-01-08', 'Kain Sejahtera',    'SJ-IN-003', 'KSJ-2025-001'],
            ['BHN-002', 250, 18500, '2025-06-12', 'Kain Sejahtera',    'SJ-IN-004', 'KSJ-2025-002'],
            ['BHN-003', 600,  9000, '2025-07-01', 'Bahan Jaya Textil', 'SJ-IN-005', 'BJT-2025-001'],
            ['BHN-003', 200,  9200, '2026-01-15', 'Bahan Jaya Textil', 'SJ-IN-006', 'BJT-2026-001'],
            ['BHN-004', 350, 22000, '2025-02-14', 'Tekstil Nusantara', 'SJ-IN-007', 'NTA-2025-003'],
            ['BHN-004', 150, 22500, '2026-02-20', 'Kain Sejahtera',    'SJ-IN-008', 'KSJ-2026-001'],
        ];

        $bahanMasukNota = []; // no_nota => total tagihan
        foreach ($bahanMasukRows as [$kode, $yard, $rp, $tgl, $supp, $sj, $nota]) {
            $total = $yard * $rp;
            BahanMasuk::create([
                'tanggal'        => $tgl,
                'no_surat_jalan' => $sj,
                'no_nota'        => $nota,
                'supplier'       => $supp,
                'kode_bahan'     => $kode,
                'nama_bahan'     => match ($kode) {
                    'BHN-001' => 'Kain Batik Tulis',
                    'BHN-002' => 'Kain Polo Pique',
                    'BHN-003' => 'Kain Cotton Combed 30s',
                    'BHN-004' => 'Kain Twill Peach',
                },
                'yard'           => $yard,
                'rp_per_yard'    => $rp,
                'total'          => $total,
                'status'         => 'diterima',
            ]);
            $bahanMasukNota[$nota] = $total;
        }

        // ════════════════════════════════════════════════════════════════════════
        //  6. BAHAN MASUK PEMBAYARAN
        //     6 nota lunas, 2 nota baru DP (belum lunas)
        // ════════════════════════════════════════════════════════════════════════
        $bayarBahanData = [
            // [no_nota, tanggal_bayar, jumlah, metode, ket]
            // NTA-2025-001 → lunas (2 termin)
            ['NTA-2025-001', '2025-01-10', 3000000, 'transfer', 'DP 50%'],
            ['NTA-2025-001', '2025-01-20', 3000000, 'transfer', 'Pelunasan'],
            // NTA-2025-002 → lunas (1 bayar)
            ['NTA-2025-002', '2025-03-15', 3750000, 'transfer', 'Lunas'],
            // KSJ-2025-001 → lunas
            ['KSJ-2025-001', '2025-01-12', 3600000, 'cash',     'Lunas cash'],
            // KSJ-2025-002 → lunas (2 termin)
            ['KSJ-2025-002', '2025-06-15', 2000000, 'transfer', 'DP 43%'],
            ['KSJ-2025-002', '2025-06-30', 2625000, 'transfer', 'Pelunasan'],
            // BJT-2025-001 → lunas
            ['BJT-2025-001', '2025-07-05', 5400000, 'transfer', 'Lunas'],
            // BJT-2026-001 → baru DP (belum lunas)
            ['BJT-2026-001', '2026-01-20', 1000000, 'cash',     'DP 54%'],
            // NTA-2025-003 → lunas
            ['NTA-2025-003', '2025-02-20', 7700000, 'transfer', 'Lunas'],
            // KSJ-2026-001 → belum ada bayar → skip (piutang ke supplier)
        ];

        foreach ($bayarBahanData as [$nota, $tgl, $jumlah, $metode, $ket]) {
            DB::table('bahan_masuk_pembayaran')->insert([
                'no_nota'      => $nota,
                'tanggal_bayar'=> $tgl,
                'jumlah'       => $jumlah,
                'metode'       => $metode,
                'keterangan'   => $ket,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }

        // ════════════════════════════════════════════════════════════════════════
        //  7. BAHAN KELUAR  (7 pengeluaran, sesuai PO)
        // ════════════════════════════════════════════════════════════════════════
        $bahanKeluarData = [
            ['BHN-001', 180, 12000, '2025-01-12', 'BK-2025-001'],  // PO-2025-001 Kemeja Batik
            ['BHN-002', 160, 18000, '2025-02-01', 'BK-2025-002'],  // PO-2025-002 Polo Casual
            ['BHN-004', 140, 22000, '2025-03-01', 'BK-2025-003'],  // PO-2025-003 Jaket Bomber
            ['BHN-003', 250, 9000,  '2026-01-10', 'BK-2026-001'],  // PO-2026-001 Kaos Oblong
            ['BHN-004', 150, 22000, '2026-02-05', 'BK-2026-002'],  // PO-2026-002 Celana Chino
            ['BHN-001', 180, 12500, '2026-03-01', 'BK-2026-003'],  // PO-2026-003 Kemeja Flannel
            ['BHN-002', 110, 18500, '2026-03-07', 'BK-2026-004'],  // PO-2026-004 Polo Premium
        ];

        foreach ($bahanKeluarData as [$kode, $yard, $rp, $tgl, $sj]) {
            BahanKeluar::create([
                'tanggal'        => $tgl,
                'no_surat_jalan' => $sj,
                'kode_bahan'     => $kode,
                'yard'           => $yard,
                'rp_per_yard'    => $rp,
                'total'          => $yard * $rp,
            ]);
        }

        // ════════════════════════════════════════════════════════════════════════
        //  8. STOK BAHAN (snapshot)
        // ════════════════════════════════════════════════════════════════════════
        $stokData = [
            'BHN-001' => ['masuk' => 800, 'keluar' => 360],
            'BHN-002' => ['masuk' => 650, 'keluar' => 270],
            'BHN-003' => ['masuk' => 800, 'keluar' => 250],
            'BHN-004' => ['masuk' => 500, 'keluar' => 290],
        ];
        foreach ($stokData as $kode => $s) {
            StokBahan::create([
                'kode_bahan'   => $kode,
                'total_masuk'  => $s['masuk'],
                'total_keluar' => $s['keluar'],
                'sisa_stok'    => $s['masuk'] - $s['keluar'],
            ]);
        }

        // ════════════════════════════════════════════════════════════════════════
        //  9. PRODUKSI — seed per PO mengikuti flow
        //
        //  stages (urutan):
        //    potong → jahit → cuci → finishing → kantor → toko → jual_toko / jual_gudang
        // ════════════════════════════════════════════════════════════════════════
        $pos = [
            // PO yang sudah selesai & dijual
            ['po' => 'PO-2025-001', 'model' => 'Kemeja Batik Premium', 'kode' => 'BHN-001', 'pcs' => 120, 'tglBase' => '2025-01', 'stage' => 'jual_toko'],
            ['po' => 'PO-2025-002', 'model' => 'Polo Shirt Casual',    'kode' => 'BHN-002', 'pcs' => 100, 'tglBase' => '2025-02', 'stage' => 'jual_gudang'],
            ['po' => 'PO-2025-003', 'model' => 'Jaket Bomber Fleece',  'kode' => 'BHN-004', 'pcs' =>  80, 'tglBase' => '2025-03', 'stage' => 'jual_gudang'],
            // PO yang sedang dalam proses
            ['po' => 'PO-2026-001', 'model' => 'Kaos Oblong Basic',    'kode' => 'BHN-003', 'pcs' => 150, 'tglBase' => '2026-01', 'stage' => 'jual_toko'],
            ['po' => 'PO-2026-002', 'model' => 'Celana Chino Slim',    'kode' => 'BHN-004', 'pcs' =>  90, 'tglBase' => '2026-02', 'stage' => 'cuci'],
            ['po' => 'PO-2026-003', 'model' => 'Kemeja Flannel Kotak', 'kode' => 'BHN-001', 'pcs' => 110, 'tglBase' => '2026-03', 'stage' => 'jahit'],
            ['po' => 'PO-2026-004', 'model' => 'Polo Shirt Premium',   'kode' => 'BHN-002', 'pcs' =>  70, 'tglBase' => '2026-03', 'stage' => 'potong'],
        ];

        $prosesIds = []; // po => ['jahit' => id, 'cuci' => id, 'finishing' => id]

        foreach ($pos as $p) {
            $ids = $this->seedPo($p, $rekBca, $rekMandiri);
            $prosesIds[$p['po']] = $ids;
        }

        // ════════════════════════════════════════════════════════════════════════
        //  10. DEFECT  — dari proses jahit, cuci, finishing
        // ════════════════════════════════════════════════════════════════════════
        $defectData = [
            // [sumber, po, model, pcs_defect, keterangan, referensi dari prosesIds]
            ['jahit',    'PO-2025-001', 'Kemeja Batik Premium', 3, 'Jahitan tidak rapi di bagian kerah',       'jahit'],
            ['cuci',     'PO-2025-001', 'Kemeja Batik Premium', 1, 'Warna pudar setelah laundry',              'cuci'],
            ['jahit',    'PO-2025-002', 'Polo Shirt Casual',    2, 'Benang lepas di bagian kancing',           'jahit'],
            ['finishing','PO-2025-002', 'Polo Shirt Casual',    1, 'Packaging rusak',                          'finishing'],
            ['cuci',     'PO-2025-003', 'Jaket Bomber Fleece',  2, 'Bercak sabun tidak hilang',                'cuci'],
            ['finishing','PO-2026-001', 'Kaos Oblong Basic',    3, 'Sablon tidak sempurna, warna tidak rata',  'finishing'],
            ['jahit',    'PO-2026-001', 'Kaos Oblong Basic',    2, 'Ukuran tidak sesuai pola',                 'jahit'],
        ];

        foreach ($defectData as [$sumber, $po, $model, $pcs, $ket, $refKey]) {
            $refId = $prosesIds[$po][$refKey] ?? 1;
            Defect::create([
                'sumber'        => $sumber,
                'po'            => $po,
                'model'         => $model,
                'pcs_defect'    => $pcs,
                'keterangan'    => $ket,
                'referensi_id'  => $refId,
            ]);
        }

        // ════════════════════════════════════════════════════════════════════════
        //  11. PENJUALAN TAMBAHAN (bukan dari PO seeder) + pembayarannya
        // ════════════════════════════════════════════════════════════════════════
        $extraToko = [
            // [buyer, model, pcs, harga, diskon%, tanggal, no_nota, status, lunas%]
            ['Toko Makmur',    'Polo Shirt Casual',    30,  95000, null, '2025-11-10', 'NT-010', 'lunas',   100],
            ['Grosir Mandiri', 'Kaos Oblong Basic',    50,  55000, null, '2026-01-20', 'NT-011', 'piutang',  50],
            ['Toko Indah',     'Kemeja Batik Premium', 15, 185000, 5,    '2026-02-14', 'NT-012', 'lunas',   100],
            ['Toko Sejahtera', 'Polo Shirt Casual',    40,  95000, null, '2026-04-02', 'NT-013', 'lunas',   100],
            ['Retail Mode',    'Kemeja Batik Premium', 20, 185000, null, '2026-04-05', 'NT-014', 'lunas',   100],
            ['Toko Makmur',    'Kaos Oblong Basic',    60,  55000, 10,   '2026-04-08', 'NT-015', 'piutang',  30],
            ['Online Store',   'Polo Shirt Premium',   25, 115000, null, '2026-04-10', 'NT-016', 'lunas',   100],
        ];

        foreach ($extraToko as [$buyer, $model, $pcs, $harga, $diskon, $tgl, $nota, $status, $lunasPct]) {
            $subtotal    = $harga * $pcs;
            $diskonNom   = $diskon ? round($subtotal * $diskon / 100) : 0;
            $totalHarga  = $subtotal - $diskonNom;

            ProsesJual::create([
                'no_nota'      => $nota,
                'tanggal_nota' => $tgl,
                'buyer'        => $buyer,
                'model'        => $model,
                'pcs'          => $pcs,
                'harga_satuan' => $harga,
                'diskon'       => $diskon,
                'total_harga'  => $totalHarga,
                'status'       => $status,
            ]);

            // Pembayaran
            $bayar = round($totalHarga * $lunasPct / 100);
            DB::table('penjualan_pembayaran')->insert([
                'no_nota'      => $nota,
                'channel'      => 'toko',
                'tanggal_bayar'=> date('Y-m-d', strtotime($tgl . ' +3 days')),
                'jumlah'       => $bayar,
                'metode'       => $lunasPct === 100 ? 'transfer' : 'cash',
                'rekening_id'  => $lunasPct === 100 ? $rekMandiri : null,
                'keterangan'   => $lunasPct === 100 ? 'Lunas' : 'DP ' . $lunasPct . '%',
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }

        $extraGudang = [
            // [buyer, po, model, pcs, harga, diskon%, tanggal, no_nota, status, lunas%]
            ['Distributor Bali',  'PO-2025-003', 'Jaket Bomber Fleece', 20, 250000, null, '2026-04-03', 'NG-010', 'lunas',   100],
            ['Grosir Besar',      'PO-2026-001', 'Kaos Oblong Basic',   35,  55000, 5,    '2026-04-09', 'NG-011', 'piutang',  40],
            ['Fashion House',     'PO-2025-001', 'Kemeja Batik Premium',10, 185000, null, '2026-04-12', 'NG-012', 'lunas',   100],
            ['Distro Nusantara',  'PO-2025-002', 'Polo Shirt Casual',   25,  95000, null, '2026-04-15', 'NG-013', 'piutang',   0],
        ];

        foreach ($extraGudang as [$buyer, $po, $model, $pcs, $harga, $diskon, $tgl, $nota, $status, $lunasPct]) {
            $subtotal   = $harga * $pcs;
            $diskonNom  = $diskon ? round($subtotal * $diskon / 100) : 0;
            $totalHarga = $subtotal - $diskonNom;

            JualGudang::create([
                'no_nota'      => $nota,
                'tanggal_nota' => $tgl,
                'buyer'        => $buyer,
                'po'           => $po,
                'model'        => $model,
                'pcs'          => $pcs,
                'harga_satuan' => $harga,
                'diskon'       => $diskon,
                'total_harga'  => $totalHarga,
                'status'       => $status,
            ]);

            if ($lunasPct > 0) {
                $bayar = round($totalHarga * $lunasPct / 100);
                DB::table('penjualan_pembayaran')->insert([
                    'no_nota'      => $nota,
                    'channel'      => 'gudang',
                    'tanggal_bayar'=> date('Y-m-d', strtotime($tgl . ' +2 days')),
                    'jumlah'       => $bayar,
                    'metode'       => $lunasPct === 100 ? 'transfer' : 'cash',
                    'rekening_id'  => $lunasPct === 100 ? $rekBca : null,
                    'keterangan'   => $lunasPct === 100 ? 'Lunas' : 'DP ' . $lunasPct . '%',
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  Helper: seed satu PO mengikuti flow produksi
    //  Mengembalikan array id proses untuk keperluan defect referensi_id
    // ══════════════════════════════════════════════════════════════════════════
    private function seedPo(array $p, int $rekBca, int $rekMandiri): array
    {
        $stages = ['potong', 'jahit', 'cuci', 'finishing', 'kantor', 'toko', 'jual_toko', 'jual_gudang'];
        $idx    = array_search($p['stage'], $stages);

        $po      = $p['po'];
        $model   = $p['model'];
        $kode    = $p['kode'];
        $pcs     = $p['pcs'];
        $tglBase = $p['tglBase']; // 'YYYY-MM'
        $hargaFinishing = $this->hargaModel[$model]['finishing'] ?? 50000;
        $hargaJual      = $this->hargaModel[$model]['jual']      ?? 75000;
        $ids = ['jahit' => 1, 'cuci' => 1, 'finishing' => 1];

        $nextMonth    = date('Y-m', strtotime("{$tglBase}-01 +1 month"));
        $twoMonths    = date('Y-m', strtotime("{$tglBase}-01 +2 months"));

        // ── POTONG ────────────────────────────────────────────────────────────
        BahanProsesPotong::create([
            'tanggal_potong' => "{$tglBase}-10",
            'yard'           => round($pcs * 1.5, 2),
            'po'             => $po,
            'model'          => $model,
            'kode_bahan'     => $kode,
            'hasil_potongan' => $idx >= 1 ? $pcs : 0,
        ]);

        if ($idx < 1) return $ids;

        // ── JAHIT ─────────────────────────────────────────────────────────────
        $pcsJahit = (int) round($pcs * 0.97); // ~3% defect jahit
        $jahit = ProsesJahit::create([
            'tanggal_jahit'         => "{$tglBase}-15",
            'po'                    => $po,
            'model'                 => $model,
            'pcs_potongan'          => $pcs,
            'tanggal_selesai_jahit' => $idx >= 2 ? "{$tglBase}-28" : null,
            'pcs_hasil_jahit'       => $idx >= 2 ? $pcsJahit : 0,
        ]);
        $ids['jahit'] = $jahit->id;

        if ($idx < 2) return $ids;

        // ── CUCI ──────────────────────────────────────────────────────────────
        $pcsCuci = (int) round($pcsJahit * 0.98); // ~2% defect cuci
        $cuci = ProsesCuci::create([
            'tanggal_kirim_cuci'        => "{$tglBase}-29",
            'no_surat_jalan'            => "SJ-CUCI-{$po}",
            'po'                        => $po,
            'model'                     => $model,
            'pcs_kirim'                 => $pcsJahit,
            'tanggal_kembali_dari_cuci' => $idx >= 3 ? "{$nextMonth}-08" : null,
            'pcs_kembali'               => $idx >= 3 ? $pcsCuci : 0,
        ]);
        $ids['cuci'] = $cuci->id;

        if ($idx < 3) return $ids;

        // ── FINISHING ─────────────────────────────────────────────────────────
        $pcsFinishing = (int) round($pcsCuci * 0.99); // ~1% defect finishing
        $finishing = ProsesFinishing::create([
            'po'              => $po,
            'model'           => $model,
            'tanggal_proses'  => "{$nextMonth}-09",
            'pcs'             => $pcsCuci,
            'tanggal_selesai' => $idx >= 4 ? "{$nextMonth}-15" : null,
            'pcs_barang_jadi' => $idx >= 4 ? $pcsFinishing : 0,
            'harga_satuan'    => $hargaFinishing,
        ]);
        $ids['finishing'] = $finishing->id;

        if ($idx < 4) return $ids;

        // ── BARANG MASUK KANTOR ───────────────────────────────────────────────
        BarangMasukKantor::create([
            'no_surat_jalan'  => "SJ-KANTOR-{$po}",
            'tanggal_kirim'   => "{$nextMonth}-17",
            'po'              => $po,
            'model'           => $model,
            'pcs_barang_jadi' => $pcsFinishing,
            'harga_satuan'    => $hargaFinishing,
        ]);

        if ($idx < 5) return $ids;

        // ── BARANG KIRIM TOKO ─────────────────────────────────────────────────
        // 60% dikirim ke toko, 40% disimpan di gudang
        $pcsToko = (int) round($pcsFinishing * 0.6);
        BarangKirimToko::create([
            'no_surat_jalan'  => "SJ-TOKO-{$po}",
            'tanggal_kirim'   => "{$nextMonth}-19",
            'po'              => $po,
            'model'           => $model,
            'pcs_barang_jadi' => $pcsToko,
        ]);

        if ($idx < 6) return $ids;

        // ── JUAL TOKO ─────────────────────────────────────────────────────────
        if ($p['stage'] === 'jual_toko') {
            $pcsJual = (int) round($pcsToko * 0.85); // 85% laku terjual
            $nota    = "NT-TOKO-{$po}";
            $total   = $pcsJual * $hargaJual;
            ProsesJual::create([
                'no_nota'      => $nota,
                'tanggal_nota' => "{$nextMonth}-22",
                'buyer'        => 'Toko Makmur Jaya',
                'model'        => $model,
                'pcs'          => $pcsJual,
                'harga_satuan' => $hargaJual,
                'diskon'       => null,
                'total_harga'  => $total,
                'status'       => 'lunas',
            ]);
            // Pembayaran: lunas transfer dalam 5 hari
            DB::table('penjualan_pembayaran')->insert([
                'no_nota'      => $nota,
                'channel'      => 'toko',
                'tanggal_bayar'=> "{$nextMonth}-27",
                'jumlah'       => $total,
                'metode'       => 'transfer',
                'rekening_id'  => $rekMandiri,
                'keterangan'   => 'Lunas sesuai PO',
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }

        // ── JUAL GUDANG ───────────────────────────────────────────────────────
        if ($p['stage'] === 'jual_gudang') {
            $pcsGudang = $pcsFinishing - $pcsToko; // sisanya dijual dari gudang
            $nota      = "NT-GDG-{$po}";
            $total     = $pcsGudang * $hargaJual;
            JualGudang::create([
                'no_nota'      => $nota,
                'tanggal_nota' => "{$nextMonth}-24",
                'buyer'        => 'Grosir Mandiri',
                'po'           => $po,
                'model'        => $model,
                'pcs'          => $pcsGudang,
                'harga_satuan' => $hargaJual,
                'diskon'       => null,
                'total_harga'  => $total,
                'status'       => 'lunas',
            ]);
            // DP 50% cash, pelunasan transfer
            DB::table('penjualan_pembayaran')->insert([
                [
                    'no_nota'      => $nota,
                    'channel'      => 'gudang',
                    'tanggal_bayar'=> "{$nextMonth}-24",
                    'jumlah'       => round($total * 0.5),
                    'metode'       => 'cash',
                    'rekening_id'  => null,
                    'keterangan'   => 'DP 50%',
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ],
                [
                    'no_nota'      => $nota,
                    'channel'      => 'gudang',
                    'tanggal_bayar'=> "{$twoMonths}-05",
                    'jumlah'       => $total - round($total * 0.5),
                    'metode'       => 'transfer',
                    'rekening_id'  => $rekBca,
                    'keterangan'   => 'Pelunasan',
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ],
            ]);
        }

        return $ids;
    }
}
