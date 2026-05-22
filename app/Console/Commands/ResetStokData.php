<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ResetStokData extends Command
{
    protected $signature = 'data:reset-stok {--force : Skip confirmation}';

    protected $description = 'Hapus semua data stok bahan, stok gudang/barang, surat jalan, dan barcode';

    public function handle(): int
    {
        $tables = [
            'surat_jalan_garmen_items',
            'surat_jalan_garmen',
            'surat_jalan_masuk',
            'barcode_bahan',
            'stok_bahan',
            'barang_masuk_kantor',
            'barang_kirim_toko',
            'jual_gudang',
            'proses_jual',
        ];

        $this->warn('⚠️  Command ini akan MENGHAPUS SEMUA data di tabel berikut:');
        $this->newLine();
        foreach ($tables as $table) {
            $this->line("  - {$table}");
        }
        $this->newLine();

        if (!$this->option('force') && !$this->confirm('Yakin mau hapus semua data ini? Tidak bisa di-undo!')) {
            $this->info('Dibatalkan.');
            return 0;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
                $this->info("✓ Tabel '{$table}' berhasil dikosongkan.");
            } else {
                $this->warn("⚠ Tabel '{$table}' tidak ditemukan, skip.");
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->newLine();
        $this->info('🎉 Semua data stok, surat jalan, dan barcode berhasil dihapus!');

        return 0;
    }
}
