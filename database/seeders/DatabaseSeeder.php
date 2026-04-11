<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Supplier;
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
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // ── Admin user ──────────────────────────────────────────────────────
        User::create([
            'name'     => 'Admin',
            'email'    => 'admin@garmen.com',
            'password' => Hash::make('admin123'),
        ]);

        // ── Master data ──────────────────────────────────────────────────────
        // 4 fabric codes
        $bahans = [
            ['kode' => 'BHN-001', 'nama' => 'Kain Batik Tulis'],
            ['kode' => 'BHN-002', 'nama' => 'Kain Polo Pique'],
            ['kode' => 'BHN-003', 'nama' => 'Kain Cotton Combed 30s'],
            ['kode' => 'BHN-004', 'nama' => 'Kain Twill Peach'],
        ];

        // 7 POs at different production stages
        $pos = [
            ['po' => 'PO-2025-001', 'model' => 'Kemeja Batik Premium',  'kode' => 'BHN-001', 'stage' => 'jual_toko'],
            ['po' => 'PO-2025-002', 'model' => 'Polo Shirt Casual',      'kode' => 'BHN-002', 'stage' => 'jual_gudang'],
            ['po' => 'PO-2025-003', 'model' => 'Jaket Bomber Fleece',    'kode' => 'BHN-004', 'stage' => 'kantor'],
            ['po' => 'PO-2026-001', 'model' => 'Kaos Oblong Basic',      'kode' => 'BHN-003', 'stage' => 'finishing'],
            ['po' => 'PO-2026-002', 'model' => 'Celana Chino Slim',      'kode' => 'BHN-004', 'stage' => 'cuci'],
            ['po' => 'PO-2026-003', 'model' => 'Kemeja Flannel Kotak',   'kode' => 'BHN-001', 'stage' => 'jahit'],
            ['po' => 'PO-2026-004', 'model' => 'Polo Shirt Premium',     'kode' => 'BHN-002', 'stage' => 'potong'],
        ];

        $suppliers = ['Tekstil Nusantara', 'Kain Sejahtera', 'Bahan Jaya Textil'];

        // ── Supplier master data ──────────────────────────────────────────────
        Supplier::insert([
            ['nama' => 'Tekstil Nusantara',  'telepon' => '021-5551001', 'alamat' => 'Jl. Tekstil No. 12, Jakarta Barat',   'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Kain Sejahtera',      'telepon' => '022-5552002', 'alamat' => 'Jl. Kain Indah No. 5, Bandung',       'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Bahan Jaya Textil',  'telepon' => '031-5553003', 'alamat' => 'Jl. Raya Industri No. 88, Surabaya',  'created_at' => now(), 'updated_at' => now()],
        ]);

        // ── BahanMasuk ──────────────────────────────────────────────────────
        $bahanMasukData = [
            ['BHN-001', 500,  12000, '2025-01-05', $suppliers[0], 'LJ-001', 'NTA-001', 'diterima'],
            ['BHN-001', 300,  12000, '2025-03-10', $suppliers[0], 'LJ-002', 'NTA-002', 'diterima'],
            ['BHN-002', 400,  18000, '2025-01-08', $suppliers[1], 'LJ-003', 'KSJ-001', 'diterima'],
            ['BHN-002', 250,  18000, '2025-06-12', $suppliers[1], 'LJ-004', 'KSJ-002', 'diterima'],
            ['BHN-003', 600,   9000, '2025-07-01', $suppliers[2], 'LJ-005', 'BJT-001', 'diterima'],
            ['BHN-003', 200,   9000, '2026-01-15', $suppliers[2], 'LJ-006', 'BJT-002', 'diterima'],
            ['BHN-004', 350,  22000, '2025-02-14', $suppliers[0], 'LJ-007', 'NTA-003', 'diterima'],
            ['BHN-004', 150,  22000, '2026-02-20', $suppliers[1], 'LJ-008', 'KSJ-003', 'pending'],
        ];

        foreach ($bahanMasukData as [$kode, $yard, $rp, $tgl, $supp, $sj, $nota, $status]) {
            BahanMasuk::create([
                'tanggal'        => $tgl,
                'no_surat_jalan' => $sj,
                'no_nota'        => $nota,
                'supplier'       => $supp,
                'kode_bahan'     => $kode,
                'yard'           => $yard,
                'rp_per_yard'    => $rp,
                'total'          => $yard * $rp,
                'status'         => $status,
            ]);
        }

        // ── BahanKeluar (keluarkan bahan untuk tiap PO) ──────────────────────
        $bahanKeluarData = [
            ['BHN-001', 180, '2025-01-12', 'BK-001'],
            ['BHN-002', 160, '2025-01-15', 'BK-002'],
            ['BHN-004', 200, '2025-02-20', 'BK-003'],
            ['BHN-003', 220, '2025-07-05', 'BK-004'],
            ['BHN-004', 130, '2026-03-01', 'BK-005'],
            ['BHN-001', 140, '2026-03-05', 'BK-006'],
            ['BHN-002', 100, '2026-03-10', 'BK-007'],
        ];

        foreach ($bahanKeluarData as [$kode, $yard, $tgl, $sj]) {
            BahanKeluar::create(['tanggal' => $tgl, 'no_surat_jalan' => $sj, 'kode_bahan' => $kode, 'yard' => $yard]);
        }

        // ── StokBahan (manual compute) ───────────────────────────────────────
        $stokData = [
            'BHN-001' => ['masuk' => 800, 'keluar' => 320],
            'BHN-002' => ['masuk' => 650, 'keluar' => 260],
            'BHN-003' => ['masuk' => 800, 'keluar' => 220],
            'BHN-004' => ['masuk' => 500, 'keluar' => 330],
        ];
        foreach ($stokData as $kode => $s) {
            StokBahan::create([
                'kode_bahan'  => $kode,
                'total_masuk' => $s['masuk'],
                'total_keluar'=> $s['keluar'],
                'sisa_stok'   => $s['masuk'] - $s['keluar'],
            ]);
        }

        // ── Helper: seed by stage ────────────────────────────────────────────
        foreach ($pos as $p) {
            $this->seedPo($p['po'], $p['model'], $p['kode'], $p['stage']);
        }

        // ── Additional ProsesJual (jual toko - not PO based) ─────────────────
        // PO-2025-001 model sold at toko (already created inside seedPo)
        // Extra sales for a richer dashboard
        $extraJual = [
            ['Toko Makmur',    'Polo Shirt Casual',      30, 95000,  '2025-11-10', 'NT-010', 'lunas'],
            ['Grosir Mandiri', 'Kaos Oblong Basic',      50, 55000,  '2026-01-20', 'NT-011', 'pending'],
            ['Toko Indah',     'Kemeja Batik Premium',   15, 185000, '2026-02-14', 'NT-012', 'lunas'],
            // April 2026 — bulan ini
            ['Toko Sejahtera', 'Polo Shirt Casual',      40, 95000,  '2026-04-02', 'NT-013', 'lunas'],
            ['Retail Mode',    'Kemeja Batik Premium',   20, 185000, '2026-04-05', 'NT-014', 'lunas'],
            ['Toko Makmur',    'Kaos Oblong Basic',      60, 55000,  '2026-04-08', 'NT-015', 'pending'],
            ['Online Store',   'Polo Shirt Premium',     25, 110000, '2026-04-10', 'NT-016', 'lunas'],
        ];
        foreach ($extraJual as [$buyer, $model, $pcs, $harga, $tgl, $nota, $status]) {
            ProsesJual::create([
                'no_nota'       => $nota,
                'tanggal_nota'  => $tgl,
                'buyer'         => $buyer,
                'model'         => $model,
                'pcs'           => $pcs,
                'harga_satuan'  => $harga,
                'total_harga'   => $pcs * $harga,
                'status'        => $status,
            ]);
        }

        // ── Jual Gudang April 2026 ────────────────────────────────────────────
        $extraGudang = [
            ['Distributor A', 'PO-2025-003', 'Jaket Bomber Fleece', 20, 230000, '2026-04-03', 'NG-010', 'lunas'],
            ['Grosir Besar',  'PO-2026-001', 'Kaos Oblong Basic',   35,  50000, '2026-04-09', 'NG-011', 'pending'],
        ];
        foreach ($extraGudang as [$buyer, $po, $model, $pcs, $harga, $tgl, $nota, $status]) {
            JualGudang::create([
                'no_nota'      => $nota,
                'tanggal_nota' => $tgl,
                'buyer'        => $buyer,
                'po'           => $po,
                'model'        => $model,
                'pcs'          => $pcs,
                'harga_satuan' => $harga,
                'total_harga'  => $pcs * $harga,
                'status'       => $status,
            ]);
        }
    }

    private function seedPo(string $po, string $model, string $kode, string $stage): void
    {
        $stages = ['potong', 'jahit', 'cuci', 'finishing', 'kantor', 'toko', 'jual_toko', 'jual_gudang'];
        $idx = array_search($stage, $stages);

        // ── Proses Potong ─────────────────────────────────────────────────────
        $pcs = match ($po) {
            'PO-2025-001' => 120,
            'PO-2025-002' => 100,
            'PO-2025-003' => 80,
            'PO-2026-001' => 150,
            'PO-2026-002' => 90,
            'PO-2026-003' => 110,
            'PO-2026-004' => 70,
            default       => 100,
        };

        $tglBase = match ($po) {
            'PO-2025-001' => '2025-01',
            'PO-2025-002' => '2025-02',
            'PO-2025-003' => '2025-03',
            'PO-2026-001' => '2026-01',
            'PO-2026-002' => '2026-02',
            'PO-2026-003' => '2026-03',
            'PO-2026-004' => '2026-03',
            default       => '2026-01',
        };

        BahanProsesPotong::create([
            'tanggal_potong'  => "{$tglBase}-10",
            'yard'            => round($pcs * 1.5, 2),
            'po'              => $po,
            'model'           => $model,
            'kode_bahan'      => $kode,
            'hasil_potongan'  => $idx >= 1 ? $pcs : 0,
        ]);

        if ($idx < 1) return;

        // ── Proses Jahit ──────────────────────────────────────────────────────
        $pcsJahit = (int) round($pcs * 0.97);
        ProsesJahit::create([
            'tanggal_jahit'       => "{$tglBase}-15",
            'po'                  => $po,
            'model'               => $model,
            'pcs_potongan'        => $pcs,
            'tanggal_selesai_jahit' => $idx >= 2 ? "{$tglBase}-25" : null,
            'pcs_hasil_jahit'     => $idx >= 2 ? $pcsJahit : 0,
        ]);

        if ($idx < 2) return;

        // ── Proses Cuci ───────────────────────────────────────────────────────
        $pcsCuci = (int) round($pcsJahit * 0.98);
        $nextMonth = date('Y-m', strtotime("{$tglBase}-01 +1 month"));
        ProsesCuci::create([
            'tanggal_kirim_cuci'       => "{$tglBase}-26",
            'no_surat_jalan'           => "SJ-CUCI-{$po}",
            'po'                       => $po,
            'model'                    => $model,
            'pcs_kirim'                => $pcsJahit,
            'tanggal_kembali_dari_cuci'=> $idx >= 3 ? "{$nextMonth}-05" : null,
            'pcs_kembali'              => $idx >= 3 ? $pcsCuci : 0,
        ]);

        if ($idx < 3) return;

        // ── Proses Finishing ──────────────────────────────────────────────────
        $pcsFinishing = (int) round($pcsCuci * 0.99);
        ProsesFinishing::create([
            'po'             => $po,
            'model'          => $model,
            'tanggal_proses' => "{$nextMonth}-06",
            'pcs'            => $pcsCuci,
            'tanggal_selesai'=> $idx >= 4 ? "{$nextMonth}-12" : null,
            'pcs_barang_jadi'=> $idx >= 4 ? $pcsFinishing : 0,
        ]);

        if ($idx < 4) return;

        // ── Barang Masuk Kantor ───────────────────────────────────────────────
        BarangMasukKantor::create([
            'no_surat_jalan' => "SJ-KANTOR-{$po}",
            'tanggal_kirim'  => "{$nextMonth}-14",
            'po'             => $po,
            'model'          => $model,
            'pcs_barang_jadi'=> $pcsFinishing,
        ]);

        if ($idx < 5) return;

        // ── Barang Kirim Toko ─────────────────────────────────────────────────
        $pcsToko = (int) round($pcsFinishing * 0.6);
        BarangKirimToko::create([
            'no_surat_jalan' => "SJ-TOKO-{$po}",
            'tanggal_kirim'  => "{$nextMonth}-16",
            'po'             => $po,
            'model'          => $model,
            'pcs_barang_jadi'=> $pcsToko,
        ]);

        if ($idx < 6) return;

        // ── Jual Toko ─────────────────────────────────────────────────────────
        if ($stage === 'jual_toko') {
            $harga = match ($model) {
                'Kemeja Batik Premium' => 185000,
                'Polo Shirt Casual'    => 95000,
                'Jaket Bomber Fleece'  => 250000,
                default                => 75000,
            };
            $pcsJual = (int) round($pcsToko * 0.8);
            ProsesJual::create([
                'no_nota'      => "NT-TOKO-{$po}",
                'tanggal_nota' => "{$nextMonth}-20",
                'buyer'        => 'Toko Makmur',
                'model'        => $model,
                'pcs'          => $pcsJual,
                'harga_satuan' => $harga,
                'total_harga'  => $pcsJual * $harga,
                'status'       => 'lunas',
            ]);
        }

        // ── Jual Gudang ───────────────────────────────────────────────────────
        if ($stage === 'jual_gudang') {
            $harga = match ($model) {
                'Polo Shirt Casual' => 88000,
                default             => 70000,
            };
            $pcsGudang = $pcsFinishing - $pcsToko;
            JualGudang::create([
                'no_nota'      => "NT-GDG-{$po}",
                'tanggal_nota' => "{$nextMonth}-22",
                'buyer'        => 'Grosir Mandiri',
                'po'           => $po,
                'model'        => $model,
                'pcs'          => $pcsGudang,
                'harga_satuan' => $harga,
                'total_harga'  => $pcsGudang * $harga,
                'status'       => 'lunas',
            ]);
        }
    }
}

