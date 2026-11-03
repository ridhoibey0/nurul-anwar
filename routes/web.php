<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ArtikelAdminController;
use App\Http\Controllers\Admin\PendaftaranAdminController;
use App\Http\Controllers\Admin\GaleriController;
use App\Http\Controllers\Admin\PengaturanController;
use App\Http\Controllers\ArtikelController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PendaftaranController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Halaman publik
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/sitemap.xml', [SitemapController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');

Route::get('/blog', [ArtikelController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [ArtikelController::class, 'show'])->name('blog.show');

Route::prefix('ppdb')->name('ppdb.')->group(function (): void {
    Route::get('/', [PendaftaranController::class, 'info'])->name('info');

    Route::get('/daftar', [PendaftaranController::class, 'create'])->name('create');
    Route::post('/daftar', [PendaftaranController::class, 'store'])
        ->middleware('throttle:10,60')
        ->name('store');

    Route::get('/selesai', [PendaftaranController::class, 'selesai'])->name('selesai');

    Route::get('/cek-status', [PendaftaranController::class, 'cek'])->name('cek');
    Route::post('/cek-status', [PendaftaranController::class, 'cekProses'])
        ->middleware('throttle:20,60')
        ->name('cek.proses');

    /*
    | Unduh bukti pendaftaran sebagai PDF. Sengaja POST, bukan GET: nomor
    | pendaftaran dan tanggal lahir anak jadi tidak ikut tercatat di riwayat
    | peramban, log server, maupun header Referer saat tautannya dibuka.
    */
    Route::post('/bukti', [PendaftaranController::class, 'bukti'])
        ->middleware('throttle:20,60')
        ->name('bukti');
});

/*
|--------------------------------------------------------------------------
| Panel admin / panitia PPDB
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])
            ->middleware('throttle:10,1')
            ->name('login.store');
    });

    Route::middleware('auth')->group(function (): void {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/pendaftaran', [PendaftaranAdminController::class, 'index'])
            ->name('pendaftaran.index');
        Route::get('/pendaftaran/export', [PendaftaranAdminController::class, 'export'])
            ->name('pendaftaran.export');
        Route::get('/pendaftaran/{pendaftaran}', [PendaftaranAdminController::class, 'show'])
            ->name('pendaftaran.show');
        Route::patch('/pendaftaran/{pendaftaran}/status', [PendaftaranAdminController::class, 'updateStatus'])
            ->name('pendaftaran.status');
        Route::delete('/pendaftaran/{pendaftaran}', [PendaftaranAdminController::class, 'destroy'])
            ->name('pendaftaran.destroy');
        Route::get('/pendaftaran/{pendaftaran}/berkas/{jenis}', [PendaftaranAdminController::class, 'berkas'])
            ->name('pendaftaran.berkas');

        Route::get('/artikel', [ArtikelAdminController::class, 'index'])->name('artikel.index');
        Route::get('/artikel/baru', [ArtikelAdminController::class, 'create'])->name('artikel.create');
        Route::post('/artikel', [ArtikelAdminController::class, 'store'])->name('artikel.store');
        Route::get('/artikel/{artikel}/ubah', [ArtikelAdminController::class, 'edit'])->name('artikel.edit');
        Route::put('/artikel/{artikel}', [ArtikelAdminController::class, 'update'])->name('artikel.update');
        Route::delete('/artikel/{artikel}', [ArtikelAdminController::class, 'destroy'])->name('artikel.destroy');

        Route::get('/galeri', [GaleriController::class, 'index'])->name('galeri.index');
        Route::post('/galeri', [GaleriController::class, 'store'])->name('galeri.store');
        Route::put('/galeri/{galeri}', [GaleriController::class, 'update'])->name('galeri.update');
        Route::post('/galeri/{galeri}/geser', [GaleriController::class, 'geser'])->name('galeri.geser');
        Route::delete('/galeri/{galeri}', [GaleriController::class, 'destroy'])->name('galeri.destroy');

        Route::get('/pengaturan/sekolah', [PengaturanController::class, 'editSekolah'])
            ->name('pengaturan.sekolah');
        Route::patch('/pengaturan/sekolah', [PengaturanController::class, 'updateSekolah'])
            ->name('pengaturan.sekolah.update');

        Route::get('/pengaturan', [PengaturanController::class, 'edit'])->name('pengaturan.edit');
        Route::patch('/pengaturan', [PengaturanController::class, 'update'])->name('pengaturan.update');
        Route::post('/pengaturan/saklar', [PengaturanController::class, 'toggle'])->name('pengaturan.toggle');
    });
});
