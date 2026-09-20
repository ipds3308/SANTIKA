<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\NewsController;

// ==========================================
// 1. RUTE PUBLIK (Siapa saja boleh akses)
// ==========================================
Route::get('/', [RegistrationController::class, 'create'])->name('home');
Route::get('/pendaftaran', [RegistrationController::class, 'create'])->name('pendaftaran.create');
Route::post('/pendaftaran', [RegistrationController::class, 'store'])->name('pendaftaran.store');
Route::get('/pendaftaran/cetak/{id}', [RegistrationController::class, 'cetak_pdf'])->name('pendaftaran.cetak');

// Rute Ruang Tunggu Publik (Untuk Layar TV / Monitor Ruang Tunggu)
Route::get('/ruang-tunggu', [RegistrationController::class, 'ruangTunggu'])->name('ruang.tunggu');
Route::get('/api/ruang-tunggu-data', [RegistrationController::class, 'apiRuangTunggu'])->name('api.ruang.tunggu');
Route::get('/api/berita', [NewsController::class, 'api'])->name('api.berita');
Route::get('/assets/santika-logo', function () {
    return response()->file(base_path('app/asset/SANTIKA LOGO.png'));
})->name('assets.santika-logo');
Route::get('/assets/santika-logo-hitam', function () {
    return response()->file(base_path('app/asset/LOGO SANTIKA HITAM.png'));
})->name('assets.santika-logo-hitam');


// ==========================================
// 2. RUTE LOGIN ADMIN & CS
// ==========================================
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'authenticate']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');


// ==========================================
// 3. RUTE ADMIN & CS TERKUNCI (Wajib Login)
// ==========================================
Route::middleware(['auth'])->group(function () {
    // Dasbor utama
    Route::get('/admin', [RegistrationController::class, 'index'])->name('admin.dashboard');
    Route::get('/berita', [NewsController::class, 'index'])->name('news.index');
    Route::get('/admin/berita', [NewsController::class, 'index'])->name('admin.news.index');
    Route::post('/berita', [NewsController::class, 'store'])->name('news.store');
    Route::post('/admin/berita', [NewsController::class, 'store'])->name('admin.news.store');
    Route::put('/berita/{id}', [NewsController::class, 'edit'])->name('news.edit');
    Route::put('/admin/berita/{id}', [NewsController::class, 'edit'])->name('admin.news.edit');
    
    // Ubah status antrian (Panggil/Selesai) via Link
    Route::get('/admin/status/{id}/{status}', [RegistrationController::class, 'updateStatus'])->name('admin.status');
    
    // Aksi Panggil Antrian khusus AJAX (Untuk Trigger Suara dan Monitor Ruang Tunggu)
    Route::post('/admin/panggil/{id}', [RegistrationController::class, 'panggilAntrian'])->name('admin.panggil');
    
    // Edit dan hapus data (khusus Admin)
    Route::put('/admin/antrian/{id}', [RegistrationController::class, 'update'])->name('admin.update');
    Route::delete('/admin/hapus/{id}', [RegistrationController::class, 'destroy'])->name('admin.hapus');
    
    // Export Excel/CSV
    Route::get('/admin/export', [RegistrationController::class, 'export'])->name('admin.export');
    
    // Export Laporan Word (.doc)
    Route::get('/admin/export-word', [RegistrationController::class, 'exportWord'])->name('admin.exportWord');

    // Export Laporan PDF
    Route::get('/admin/export-pdf', [RegistrationController::class, 'exportPdf'])->name('admin.exportPdf');

    // Rute Manajemen Pengguna (Dilindungi oleh middleware auth & pengaman role di UserController)
    Route::get('/rahasia/bps-manajemen-akun', [UserController::class, 'index'])->name('admin.users.index');
    Route::post('/rahasia/bps-manajemen-akun/store', [UserController::class, 'store'])->name('admin.users.store');
    Route::put('/rahasia/bps-manajemen-akun/update-role/{id}', [UserController::class, 'updateRole'])->name('admin.users.updateRole');
    Route::delete('/rahasia/bps-manajemen-akun/delete/{id}', [UserController::class, 'destroy'])->name('admin.users.destroy');
});


// ==========================================
// 4. BAWAAN LARAVEL (Inertia & Settings)
// ==========================================
Route::get('dashboard', [RegistrationController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';