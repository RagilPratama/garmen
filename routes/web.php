<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BahanMasukController;
use App\Http\Controllers\BahanMasukPembayaranController;
use App\Http\Controllers\BahanKeluarController;
use App\Http\Controllers\BahanProsesPotongController;
use App\Http\Controllers\ProsesJahitController;
use App\Http\Controllers\ProsesCuciController;
use App\Http\Controllers\ProsesFinishingController;
use App\Http\Controllers\BarangMasukKantorController;
use App\Http\Controllers\BarangKirimTokoController;
use App\Http\Controllers\ProsesJualController;
use App\Http\Controllers\JualGudangController;
use App\Http\Controllers\StokBahanController;
use App\Http\Controllers\StokBarangController;
use App\Http\Controllers\MasterModelController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\RekeningController;
use App\Http\Controllers\TrackingPoController;
use Illuminate\Support\Facades\Route;

// Auth routes
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Protected routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('master-model', MasterModelController::class)->except(['show', 'create', 'edit']);
    Route::resource('supplier', SupplierController::class)->except(['show', 'create', 'edit']);
    Route::resource('rekening', RekeningController::class)->except(['show', 'create', 'edit']);
    Route::resource('bahan-masuk', BahanMasukController::class)->except(['show']);
    Route::post('/bahan-masuk/{noNota}/pembayaran', [BahanMasukPembayaranController::class, 'store'])->name('bahan-masuk.pembayaran.store');
    Route::resource('bahan-keluar', BahanKeluarController::class)->except(['show']);
    Route::post('/bahan-proses-potong/update-model', [BahanProsesPotongController::class, 'updateModel'])->name('bahan-proses-potong.update-model');
    Route::resource('bahan-proses-potong', BahanProsesPotongController::class)->except(['show']);
    Route::resource('proses-jahit', ProsesJahitController::class)->except(['show']);
    Route::resource('proses-cuci', ProsesCuciController::class)->except(['show']);
    Route::resource('proses-finishing', ProsesFinishingController::class)->except(['show']);
    Route::resource('barang-masuk-kantor', BarangMasukKantorController::class)->except(['show']);
    Route::resource('barang-kirim-toko', BarangKirimTokoController::class)->except(['show']);
    Route::resource('proses-jual', ProsesJualController::class)->except(['show']);
    Route::resource('jual-gudang', JualGudangController::class)->except(['show', 'create', 'edit']);
    Route::get('/stok-bahan', [StokBahanController::class, 'index'])->name('stok-bahan.index');
    Route::get('/stok-barang', [StokBarangController::class, 'index'])->name('stok-barang.index');
    Route::get('/tracking-po', [TrackingPoController::class, 'index'])->name('tracking-po.index');
});
