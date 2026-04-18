<?php

namespace App\Http\Controllers;

use App\Models\Defect;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DefectController extends Controller
{
    public function index()
    {
        $search = request('search');
        $sumber = request('sumber'); // filter by source

        $rows = DB::table('defects')
            ->select('id', 'sumber', 'po', 'model', 'pcs_defect', 'keterangan', 'referensi_id', 'created_at')
            ->when($search, fn($q) => $q->where(fn($q) => $q
                ->where('po',    'like', "%{$search}%")
                ->orWhere('model', 'like', "%{$search}%")
            ))
            ->when($sumber, fn($q) => $q->where('sumber', $sumber))
            ->orderByDesc('created_at')
            ->paginate(20)->withQueryString();

        $summary = DB::table('defects')
            ->selectRaw("sumber, SUM(pcs_defect) as total_defect, COUNT(*) as jumlah_kasus")
            ->groupBy('sumber')
            ->orderBy('sumber')
            ->get();

        return Inertia::render('Defect/Index', [
            'rows'    => $rows,
            'summary' => $summary,
            'sumber'  => $sumber,
        ]);
    }
}
