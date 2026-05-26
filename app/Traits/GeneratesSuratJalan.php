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
        $latest = $modelClass::where('no_surat_jalan', 'like', $prefix . '%')
            ->orderByRaw('CAST(SUBSTRING(no_surat_jalan, ' . (strlen($prefix) + 1) . ') AS UNSIGNED) DESC')
            ->first();

        if (!$latest) {
            return $prefix . str_pad(1, 4, '0', STR_PAD_LEFT);
        }

        $lastCode = $latest->no_surat_jalan;
        $numberPart = substr($lastCode, strlen($prefix));
        $lastNumber = (int)$numberPart;
        
        // Keep original padding length
        return $prefix . str_pad($lastNumber + 1, strlen($numberPart), '0', STR_PAD_LEFT);
    }

    /**
     * Generate the next sequential code for any column and prefix.
     * Uses latest number from DB + 1 with original padding.
     */
    protected function nextCode(string $modelClass, string $column, string $prefix): string
    {
        $latest = $modelClass::whereNotNull($column)
            ->where($column, 'like', $prefix . '%')
            ->orderByRaw('CAST(SUBSTRING(' . $column . ', ' . (strlen($prefix) + 1) . ') AS UNSIGNED) DESC')
            ->first();

        if (!$latest) {
            return $prefix . str_pad(1, 4, '0', STR_PAD_LEFT);
        }

        $lastCode = $latest->$column;
        $numberPart = substr($lastCode, strlen($prefix));
        $lastNumber = (int)$numberPart;

        // Keep original padding length
        return $prefix . str_pad($lastNumber + 1, strlen($numberPart), '0', STR_PAD_LEFT);
    }
}
