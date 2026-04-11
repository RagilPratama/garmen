<?php

namespace App\Http\Controllers;

use App\Models\StokBahan;
use Inertia\Inertia;

class StokBahanController extends Controller
{
    public function index()
    {
        $data = StokBahan::orderBy('kode_bahan')->paginate(50);
        return Inertia::render('StokBahan/Index', ['data' => $data]);
    }
}
