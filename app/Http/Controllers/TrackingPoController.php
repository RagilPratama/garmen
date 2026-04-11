<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\BahanProsesPotong;
use App\Models\ProsesJahit;
use App\Models\ProsesCuci;
use App\Models\ProsesFinishing;
use App\Models\BarangMasukKantor;
use App\Models\BarangKirimToko;
use App\Models\JualGudang;

class TrackingPoController extends Controller
{
    private array $stageOrder = ['potong', 'jahit', 'cuci', 'finishing', 'kantor', 'toko', 'jual'];

    private array $stageLabels = [
        'potong'    => 'Proses Potong',
        'jahit'     => 'Proses Jahit',
        'cuci'      => 'Proses Cuci',
        'finishing' => 'Finishing',
        'kantor'    => 'Masuk Kantor',
        'toko'      => 'Kirim Toko',
        'jual'      => 'Jual Gudang',
    ];

    public function index()
    {
        // Collect all unique PO+model combinations across all stages
        $allCombos = collect();

        $models = [
            BahanProsesPotong::class,
            ProsesJahit::class,
            ProsesCuci::class,
            ProsesFinishing::class,
            BarangMasukKantor::class,
            BarangKirimToko::class,
            JualGudang::class,
        ];

        foreach ($models as $model) {
            $model::select('po', 'model')->distinct()
                ->whereNotNull('po')->where('po', '!=', '')
                ->get()
                ->each(function ($row) use (&$allCombos) {
                    $key = $row->po . '|||' . $row->model;
                    if (!$allCombos->has($key)) {
                        $allCombos->put($key, ['po' => $row->po, 'model' => $row->model]);
                    }
                });
        }

        // Fetch all stage data grouped by po|||model
        $potongData    = BahanProsesPotong::select('po', 'model', 'hasil_potongan')->get()
            ->groupBy(fn($r) => $r->po . '|||' . $r->model);
        $jahitData     = ProsesJahit::select('po', 'model', 'tanggal_selesai_jahit')->get()
            ->groupBy(fn($r) => $r->po . '|||' . $r->model);
        $cuciData      = ProsesCuci::select('po', 'model', 'tanggal_kembali_dari_cuci')->get()
            ->groupBy(fn($r) => $r->po . '|||' . $r->model);
        $finishingData = ProsesFinishing::select('po', 'model', 'tanggal_selesai')->get()
            ->groupBy(fn($r) => $r->po . '|||' . $r->model);
        $kantorData    = BarangMasukKantor::select('po', 'model')->get()
            ->groupBy(fn($r) => $r->po . '|||' . $r->model);
        $tokoData      = BarangKirimToko::select('po', 'model')->get()
            ->groupBy(fn($r) => $r->po . '|||' . $r->model);
        $jualData      = JualGudang::select('po', 'model', 'status')->get()
            ->groupBy(fn($r) => $r->po . '|||' . $r->model);

        $result = $allCombos->values()->map(function ($combo) use (
            $potongData, $jahitData, $cuciData, $finishingData, $kantorData, $tokoData, $jualData
        ) {
            $key = $combo['po'] . '|||' . $combo['model'];

            $potong    = $potongData->get($key);
            $jahit     = $jahitData->get($key);
            $cuci      = $cuciData->get($key);
            $finishing = $finishingData->get($key);
            $kantor    = $kantorData->get($key);
            $toko      = $tokoData->get($key);
            $jual      = $jualData->get($key);

            // done = completed, active = in progress, pending = not started yet
            $stages = [
                'potong'    => $potong    ? ($potong->filter(fn($r) => $r->hasil_potongan > 0)->isNotEmpty() ? 'done' : 'active') : 'pending',
                'jahit'     => $jahit     ? ($jahit->filter(fn($r) => $r->tanggal_selesai_jahit !== null)->isNotEmpty() ? 'done' : 'active') : 'pending',
                'cuci'      => $cuci      ? ($cuci->filter(fn($r) => $r->tanggal_kembali_dari_cuci !== null)->isNotEmpty() ? 'done' : 'active') : 'pending',
                'finishing' => $finishing ? ($finishing->filter(fn($r) => $r->tanggal_selesai !== null)->isNotEmpty() ? 'done' : 'active') : 'pending',
                'kantor'    => $kantor ? 'done' : 'pending',
                'toko'      => $toko   ? 'done' : 'pending',
                'jual'      => $jual   ? ($jual->filter(fn($r) => $r->status === 'lunas')->isNotEmpty() ? 'done' : 'active') : 'pending',
            ];

            // Determine current stage (last active, or next after last done)
            $currentStage = 'pending';
            $currentLabel = 'Belum Mulai';

            if ($stages['jual'] === 'done') {
                $currentStage = 'selesai';
                $currentLabel = 'Selesai';
            } else {
                foreach (array_reverse($this->stageOrder) as $s) {
                    if ($stages[$s] === 'active') {
                        $currentStage = $s;
                        $currentLabel = $this->stageLabels[$s];
                        break;
                    }
                    if ($stages[$s] === 'done') {
                        $idx = array_search($s, $this->stageOrder);
                        $nextKey = $this->stageOrder[$idx + 1] ?? $s;
                        $currentStage = $nextKey;
                        $currentLabel = $this->stageLabels[$nextKey] ?? $this->stageLabels[$s];
                        break;
                    }
                }
            }

            return [
                'po'            => $combo['po'],
                'model'         => $combo['model'],
                'stages'        => $stages,
                'current_stage' => $currentStage,
                'current_label' => $currentLabel,
            ];
        })->sortBy([['po', 'asc'], ['model', 'asc']])->values();

        // Summary counts per stage (for the header stat cards)
        $summary = [];
        foreach ($this->stageOrder as $stage) {
            $summary[$stage] = $result->filter(fn($r) => $r['current_stage'] === $stage)->count();
        }
        $summary['selesai'] = $result->filter(fn($r) => $r['current_stage'] === 'selesai')->count();
        $summary['pending'] = $result->filter(fn($r) => $r['current_stage'] === 'pending')->count();

        return Inertia::render('TrackingPo/Index', [
            'trackingData' => $result,
            'summary'      => $summary,
            'stageLabels'  => $this->stageLabels,
        ]);
    }
}
