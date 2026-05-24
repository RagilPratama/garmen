<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterBahanSeeder extends Seeder
{
    public function run(): void
    {
        $jenisBahan = [
            'ABU',
            'AO',
            'BW',
            'CAMPUR',
            'CORDURAY',
            'CORDURAY ACEAN',
            'CORDURAY BUNGLON',
            'CORDURAY NAVY MOTIF',
            'CORDURAY KOTAK',
            'CORDURAY SIKABU TEX',
            'CRINGKLE',
            'DEKA TEXT JEANS',
            'DENIM',
            'DENIM HITAM',
            'DENIM HITAM DAN MOTIF',
            'DENIM KOTAK',
            'DENIM MOTIF DAUN',
            'DENIM SALUR',
            'FLANEL',
            'FLANEL DEKA TEX',
            'FLANEL KOTAK',
            'GL CREAM',
            'GL HITAM',
            'GL MERAH',
            'GL NAVY',
            'HIJAU MOTIF DINO',
            'HITAM',
            'HITAM MOTIF',
            'HITAM PUTIH',
            'KANVAS',
            'KANVAS HT',
            'KOTAK',
            'KOTAK KOTAK',
            'KOTAK YANDED',
            'KOTAK-KOTAK',
            'KUNING',
            'Katun Taiwan',
            'LINEN',
            'LORENG',
            'MERAH',
            'MOTIF',
            'MOTIF HITAM',
            'MOTIF HITAM PUTIH',
            'MOTIF KELAPA',
            'MOTIF ORANGE',
            'NAVY',
            'ORANGE',
            'OXFORD',
            'POLINOSIK',
            'POPLIN HT',
            'PULKADOT DOBEL PINK',
            'PUTIH',
            'RAFLE MOTIF IQBAL',
            'RAMI',
            'RIPSTOK',
            'SALEM',
            'SALUR',
            'SALUR NAVY',
            'SALUR PUTIH HIJAU',
            'SALUR PUTIH NAVY',
            'SIKABU TEX CORDURAY',
            'SNH SUEDE',
            'SOFT DENIM BIRU',
            'TWILL',
            'TWILL RAYON',
            'TWILL ANTON',
            'TWILL BW',
            'TWILL HT',
            'TWILL HT 180',
            'TWILL HT 181',
            'TWILL HT 182',
            'TWILL HT 183',
            'TWILL HT 184',
            'TWILL STREET',
            'TWILL STREET SERAT MIRING HT',
            'WAFLE',
        ];

        $data = [];
        $now = now();

        foreach ($jenisBahan as $nama) {
            $data[] = [
                'nama_bahan' => $nama,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Insert batch untuk performa lebih baik
        DB::table('master_bahan')->insert($data);

        $this->command->info('✅ Berhasil menambahkan ' . count($jenisBahan) . ' jenis bahan ke master_bahan');
    }
}
