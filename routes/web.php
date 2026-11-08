<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AIChatController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PublicController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/cari', [PublicController::class, 'search'])->name('search');
Route::get('/arsip', [PublicController::class, 'archive'])->name('archive');
Route::get('/halaman/{slug}', [PublicController::class, 'legacyPage'])->name('legacy.page');
Route::get('/pengaduan', [PublicController::class, 'complaint'])->name('complaint');
Route::post('/pengaduan', [PublicController::class, 'storeComplaint'])->middleware('throttle:5,10');
Route::post('/asisten', [AIChatController::class, '__invoke'])->middleware('throttle:20,1');
Route::get('/admin/login', [AuthController::class, 'login'])->name('login');
Route::post('/admin/login', [AuthController::class, 'authenticate'])->middleware('throttle:5,1');
Route::post('/admin/logout', [AuthController::class, 'logout'])->middleware('auth');
Route::prefix('admin')->middleware('auth')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard']);
    Route::match(['get', 'post'], '/settings', [AdminController::class, 'settings']);
    Route::get('/complaints-export', [AdminController::class, 'exportComplaints']);
    Route::get('/export/{module}', [AdminController::class, 'export']);
    Route::post('/backup', [AdminController::class, 'backup']);
    Route::get('/preview/{module}/{id}', [AdminController::class, 'preview']);
    Route::post('/editor-upload', [AdminController::class, 'editorUpload'])->middleware('throttle:30,1');
    Route::get('/{module}', [AdminController::class, 'index']);
    Route::get('/{module}/create', [AdminController::class, 'create']);
    Route::post('/{module}', [AdminController::class, 'store']);
    Route::get('/{module}/{id}/edit', [AdminController::class, 'edit']);
    Route::put('/{module}/{id}', [AdminController::class, 'update']);
    Route::delete('/{module}/{id}', [AdminController::class, 'destroy']);
});
Route::get('/{type}', [PublicController::class, 'listing'])->where('type', 'berita|layanan|proyek|transparansi');
Route::get('/{type}/{slug}', [PublicController::class, 'detail'])->where('type', 'berita|layanan|proyek|transparansi');
Route::get('/{slug}', [PublicController::class, 'page'])->where('slug', 'profil|visi-misi|tugas-fungsi|struktur-organisasi|kontak|galeri|agenda');
