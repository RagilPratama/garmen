<?php

namespace App\Traits;

trait GeneratesSuratJalan
{
    /**
     * Generate the next sequential surat jalan code for a given prefix.
     * e.g. nextSuratJalan(BahanMasuk::class, 'LJ-') → 'LJ-009'
     */
    protected function nextSuratJalan(string $modelClass, string $prefix): string
    {
        $n = $modelClass::where('no_surat_jalan', 'like', $prefix . '%')->count() + 1;
        return $prefix . str_pad($n, 3, '0', STR_PAD_LEFT);
    }
}
