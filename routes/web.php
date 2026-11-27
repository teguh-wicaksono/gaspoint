<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\PembeliController;
use App\Http\Controllers\PesananController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StokKeluarController;
use App\Http\Controllers\StokMasukController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Public: landing page portal pengelola
Route::get('/', [AuthController::class, 'welcome'])->name('welcome');
Route::get('/dashboard/pembeli', [PembeliController::class, 'index'])->name('dashboard.pembeli');
Route::post('/dashboard/pembeli', [PembeliController::class, 'store'])->middleware('throttle:10,1')->name('pembeli.store');

// Guest only: login
Route::middleware('guest')->controller(AuthController::class)->group(function () {
    Route::get('/login', 'showLogin')->name('login');
    Route::post('/login', 'login')->name('login.post');
});

// Authenticated
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::middleware('role:kasir,pengelola')->prefix('kasir')->name('kasir.')->controller(KasirController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/online', 'online')->name('online');
    });
    Route::get('/kurir/rute', [DashboardController::class, 'rute'])->middleware('role:kurir,pengelola')->name('kurir.rute');
    Route::prefix('dashboard')->name('dashboard.')->controller(DashboardController::class)->group(function () {
        Route::get('/notifications', 'notifications')->name('notifications');
        Route::post('/notifications/{notificationId}/read', 'markAsRead')->name('notifications.read');
    });

    Route::controller(BarangController::class)->prefix('barang')->name('barang.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{barang}/edit', 'edit')->name('edit');
        Route::put('/{barang}', 'update')->name('update');
        Route::delete('/{barang}', 'destroy')->name('destroy');
        Route::post('/{barang}/update-stock', 'updateStock')->name('update-stock');
        Route::get('/api/data', 'getBarangData')->name('api.data');
    });

    Route::prefix('pesanan')->name('pesanan.')->controller(PesananController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{pesanan}', 'show')->name('show');
        Route::post('/{pesanan}/status', 'updateStatus')->name('status');
        Route::post('/{pesanan}/setujui', 'setujui')->middleware('role:kasir,pengelola')->name('setujui');
        Route::delete('/{pesanan}', 'destroy')->name('destroy');
    });

    Route::prefix('stok-masuk')->name('stok.masuk.')->controller(StokMasukController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{stok_masuk}/edit', 'edit')->name('edit');
        Route::put('/{stok_masuk}', 'update')->name('update');
        Route::delete('/{stok_masuk}', 'destroy')->name('destroy');
    });

    Route::prefix('stok-keluar')->name('stok.keluar.')->controller(StokKeluarController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{stok_keluar}/edit', 'edit')->name('edit');
        Route::put('/{stok_keluar}', 'update')->name('update');
        Route::delete('/{stok_keluar}', 'destroy')->name('destroy');
    });

    // Kelola Pengguna - khusus pengelola
    Route::middleware('role:pengelola')->prefix('pengguna')->name('pengguna.')->controller(UserController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{user}/edit', 'edit')->name('edit');
        Route::put('/{user}', 'update')->name('update');
        Route::delete('/{user}', 'destroy')->name('destroy');
    });

    Route::prefix('profile')->name('profile.')->controller(ProfileController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/edit', 'edit')->name('edit');
        Route::put('/update', 'update')->name('update');
        Route::put('/update-password', 'updatePassword')->name('update-password');
    });
});

Route::fallback(function () {
    if (request()->expectsJson()) {
        return response()->json(['error' => 'Not Found'], 404);
    }
    return response()->view('errors.404', [], 404);
})->name('fallback');
