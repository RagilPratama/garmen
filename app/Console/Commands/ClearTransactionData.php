<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ClearTransactionData extends Command
{
    protected $signature = 'data:clear-transactions {--force : Force delete tanpa konfirmasi}';
    protected $description = 'Hapus semua data transaksi, keep semua data master (termasuk master bahan)';

    public function handle()
    {
        $this->info('🔥 Menghapus semua data transaksi...');
        $this->newLine();

        $this->warn('⚠️  PERINGATAN KERAS!');
        $this->warn('Ini akan menghapus:');
        $this->line('   ❌ SEMUA data transaksi (Barcode, Bahan Masuk/Keluar, Stok, Produksi, Penjualan, Kas)');
        $this->newLine();
        $this->info('Yang TETAP ADA:');
        $this->line('   ✓ Users');
        $this->line('   ✓ Master Model');
        $this->line('   ✓ Master Bahan');
        $this->line('   ✓ Master Kepemilikan');
        $this->line('   ✓ Toko');
        $this->line('   ✓ Rekening');
        $this->line('   ✓ Supplier');
        $this->newLine();

        if (!$this->option('force')) {
            if (!$this->confirm('Apakah Anda YAKIN ingin melanjutkan?', false)) {
                $this->info('❌ Dibatalkan.');
                return 0;
            }

            if (!$this->confirm('Konfirmasi sekali lagi: HAPUS SEMUA data transaksi (Master Bahan TIDAK akan dihapus)?', false)) {
                $this->info('❌ Dibatalkan.');
                return 0;
            }
        } else {
            $this->warn('🚀 Mode FORCE aktif - melewati konfirmasi!');
            $this->newLine();
        }

        $this->newLine();
        $this->info('Memulai proses penghapusan...');
        $this->newLine();

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // 1. DATA TRANSAKSI - BARCODE & BAHAN MASUK
        $this->clearTable('barcode_bahan', 'Barcode Bahan');
        $this->clearTable('surat_jalan_masuk', 'Surat Jalan Masuk');
        $this->clearTable('bahan_masuk', 'Bahan Masuk (Old)');
        $this->clearTable('bahan_masuk_pembayaran', 'Pembayaran Supplier');

        // 2. BAHAN KELUAR & PROSES
        $this->clearTable('surat_jalan_garmen_items', 'Surat Jalan Garmen Items');
        $this->clearTable('surat_jalan_garmen', 'Surat Jalan Garmen');
        $this->clearTable('bahan_keluar', 'Bahan Keluar (Old)');
        $this->clearTable('bahan_proses_potong', 'Bahan Proses Potong');

        // 3. STOK BAHAN
        $this->clearTable('stok_bahan', 'Stok Bahan');

        // 4. PROSES PRODUKSI
        $this->clearTable('proses_jahit', 'Proses Jahit');
        $this->clearTable('proses_cuci', 'Proses Cuci');
        $this->clearTable('proses_finishing', 'Proses Finishing');
        $this->clearTable('defects', 'Defects');

        // 5. BARANG JADI
        $this->clearTable('barang_masuk_kantor', 'Barang Masuk Kantor');
        $this->clearTable('barang_kirim_toko', 'Barang Kirim Toko');

        // 6. PENJUALAN
        $this->clearTable('jual_gudang', 'Jual Gudang');
        $this->clearTable('proses_jual', 'Proses Jual (Toko)');
        $this->clearTable('penjualan_pembayaran', 'Penjualan Pembayaran');

        // 7. KAS & KEUANGAN
        $this->clearTable('kas_toko', 'Kas Toko');
        $this->clearTable('kas_gudang', 'Kas Gudang');
        $this->clearTable('kas_garmen', 'Kas Garmen');
        $this->clearTable('kas_transfer', 'Kas Transfer');
        $this->clearTable('kas_transfer_gudang_garmen', 'Kas Transfer Gudang-Garmen');
        $this->clearTable('pengeluaran_toko', 'Pengeluaran Toko');

        // Update saldo kas toko ke 0
        if (Schema::hasTable('tokos')) {
            DB::table('tokos')->update([
                'saldo_kas' => 0,
                'saldo_cash' => 0,
                'saldo_transfer' => 0,
                'saldo_debit' => 0,
            ]);
            $this->line('   ✓ Reset saldo kas toko');
        }

        // Update saldo kas system
        if (Schema::hasTable('saldo_kas')) {
            DB::table('saldo_kas')->update([
                'saldo_cash' => 0,
                'saldo_transfer' => 0,
            ]);
            $this->line('   ✓ Reset saldo kas system');
        }

        // 8. MASTER BAHAN (TIDAK DIHAPUS - Keep data master bahan)
        // $this->clearTable('master_bahan', 'Master Bahan');
        $this->line('   ⊙ Master Bahan: TIDAK DIHAPUS (data master dijaga)');

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->newLine();
        $this->info('✅ Selesai! Semua data transaksi telah dihapus.');
        $this->newLine();
        $this->info('📋 Data Master yang TETAP ADA:');
        $this->line('   ✓ Users (Manajemen User)');
        $this->line('   ✓ Master Model');
        $this->line('   ✓ Master Bahan (76 items)');
        $this->line('   ✓ Master Kepemilikan');
        $this->line('   ✓ Toko');
        $this->line('   ✓ Rekening');
        $this->line('   ✓ Supplier');
        $this->newLine();

        return 0;
    }

    private function clearTable(string $table, string $label)
    {
        if (Schema::hasTable($table)) {
            $count = DB::table($table)->count();
            DB::table($table)->truncate();
            $this->line("   ✓ {$label}: {$count} record dihapus");
        } else {
            $this->line("   ⊘ {$label}: table tidak ada");
        }
    }
}
