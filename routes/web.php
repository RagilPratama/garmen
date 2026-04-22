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
use App\Http\Controllers\StokBahanGarmenController;
use App\Http\Controllers\StokBarangController;
use App\Http\Controllers\MasterModelController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\RekeningController;
use App\Http\Controllers\TrackingPoController;
use App\Http\Controllers\RincianBahanController;
use App\Http\Controllers\DefectController;
use App\Http\Controllers\BarangSiapKirimController;
use App\Http\Controllers\PenjualanPembayaranController;
use App\Http\Controllers\LaporanPenjualanController;
use App\Http\Controllers\LaporanModelTerjualController;
use App\Http\Controllers\LaporanHppController;
use App\Http\Controllers\PengeluaranTokoController;
use App\Http\Controllers\KasTokoController;
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
    Route::delete('/bahan-masuk/pembayaran/{pembayaran}', [BahanMasukPembayaranController::class, 'destroy'])->name('bahan-masuk.pembayaran.destroy');
    Route::resource('bahan-keluar', BahanKeluarController::class)->except(['show']);
    Route::post('/bahan-proses-potong/update-model', [BahanProsesPotongController::class, 'updateModel'])->name('bahan-proses-potong.update-model');
    Route::resource('bahan-proses-potong', BahanProsesPotongController::class)->except(['show']);
    Route::resource('proses-jahit', ProsesJahitController::class)->except(['show']);
    Route::resource('proses-cuci', ProsesCuciController::class)->except(['show']);
    Route::resource('proses-finishing', ProsesFinishingController::class)->except(['show']);
    Route::resource('barang-masuk-kantor', BarangMasukKantorController::class)->except(['show']);
    Route::resource('barang-kirim-toko', BarangKirimTokoController::class)->except(['show']);
    Route::resource('proses-jual', ProsesJualController::class)->except(['show', 'create', 'edit']);
    Route::resource('jual-gudang', JualGudangController::class)->except(['show', 'create', 'edit']);
    Route::put('/jual-gudang-nota-status', [JualGudangController::class, 'updateNotaStatus'])->name('jual-gudang.update-nota-status');
    Route::put('/proses-jual-nota-status', [ProsesJualController::class, 'updateNotaStatus'])->name('proses-jual.update-nota-status');
    // Pembayaran penjualan (gudang & toko)
    Route::post('/penjualan-pembayaran/{noNota}', [PenjualanPembayaranController::class, 'store'])->name('penjualan-pembayaran.store');
    Route::put('/penjualan-pembayaran/{pembayaran}', [PenjualanPembayaranController::class, 'update'])->name('penjualan-pembayaran.update');
    Route::delete('/penjualan-pembayaran/{pembayaran}', [PenjualanPembayaranController::class, 'destroy'])->name('penjualan-pembayaran.destroy');
    Route::get('/stok-bahan', [StokBahanController::class, 'index'])->name('stok-bahan.index');
    Route::get('/stok-bahan-garmen', [StokBahanGarmenController::class, 'index'])->name('stok-bahan-garmen.index');
    Route::get('/stok-bahan-garmen/detail', [StokBahanGarmenController::class, 'detail'])->name('stok-bahan-garmen.detail');
    Route::get('/stok-barang', [StokBarangController::class, 'index'])->name('stok-barang.index');
    Route::get('/rincian-bahan', [RincianBahanController::class, 'index'])->name('rincian-bahan.index');
    Route::get('/defect', [DefectController::class, 'index'])->name('defect.index');
    Route::get('/tracking-po', [TrackingPoController::class, 'index'])->name('tracking-po.index');
    Route::get('/barang-siap-kirim', [BarangSiapKirimController::class, 'index'])->name('barang-siap-kirim.index');
    Route::get('/laporan-penjualan', [LaporanPenjualanController::class, 'index'])->name('laporan-penjualan.index');
    Route::get('/laporan-penjualan/export-data', [LaporanPenjualanController::class, 'exportData'])->name('laporan-penjualan.export-data');
    Route::get('/laporan-model-terjual', [LaporanModelTerjualController::class, 'index'])->name('laporan-model-terjual.index');
    Route::get('/laporan-hpp', [LaporanHppController::class, 'index'])->name('laporan-hpp.index');
    
    // Pengeluaran Toko
    Route::resource('pengeluaran-toko', PengeluaranTokoController::class)->except(['show', 'create', 'edit']);
    
    // Kas Toko
    Route::get('/kas-toko', [KasTokoController::class, 'index'])->name('kas-toko.index');
    Route::post('/kas-toko', [KasTokoController::class, 'store'])->name('kas-toko.store');
    Route::delete('/kas-toko/{kasToko}', [KasTokoController::class, 'destroy'])->name('kas-toko.destroy');
    Route::get('/kas-toko/laporan', [KasTokoController::class, 'laporan'])->name('kas-toko.laporan');
    
    // API endpoint untuk get stok toko
    Route::get('/api/stok-toko/{tokoId}', [ProsesJualController::class, 'getStokToko']);
    
    // Debug route - hapus setelah selesai debug
    Route::get('/debug-stok/{model}', function($model) {
        $finishing = \DB::table('proses_finishing')
            ->where('model', $model)
            ->select('id', 'po', 'model', 'pcs_barang_jadi', 'tanggal_selesai')
            ->get();
            
        $masukKantor = \DB::table('barang_masuk_kantor')
            ->where('model', $model)
            ->select('id', 'no_surat_jalan', 'model', 'pcs_barang_jadi', 'tanggal_kirim')
            ->get();
            
        $kirimToko = \DB::table('barang_kirim_toko')
            ->where('model', $model)
            ->select('id', 'no_surat_jalan', 'model', 'pcs_barang_jadi', 'tanggal_kirim', 'toko_id')
            ->get();
            
        return response()->json([
            'model' => $model,
            'finishing' => [
                'records' => $finishing,
                'total' => $finishing->sum('pcs_barang_jadi'),
                'count' => $finishing->count(),
            ],
            'masuk_kantor' => [
                'records' => $masukKantor,
                'total' => $masukKantor->sum('pcs_barang_jadi'),
                'count' => $masukKantor->count(),
            ],
            'kirim_toko' => [
                'records' => $kirimToko,
                'total' => $kirimToko->sum('pcs_barang_jadi'),
                'count' => $kirimToko->count(),
            ],
            'summary' => [
                'total_jadi' => $finishing->sum('pcs_barang_jadi'),
                'total_ke_kantor' => $masukKantor->sum('pcs_barang_jadi'),
                'total_ke_toko' => $kirimToko->sum('pcs_barang_jadi'),
                'total_dikirim' => $masukKantor->sum('pcs_barang_jadi') + $kirimToko->sum('pcs_barang_jadi'),
                'sisa' => $finishing->sum('pcs_barang_jadi') - ($masukKantor->sum('pcs_barang_jadi') + $kirimToko->sum('pcs_barang_jadi')),
            ],
        ]);
    });
});
