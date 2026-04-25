<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StorefrontController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [StorefrontController::class, 'index'])->name('store.index');
Route::get('/products/{slug}', [StorefrontController::class, 'show'])->name('product.show');
Route::view('/checkout', 'checkout.index')->name('checkout.index');
Route::get('/admin/dashboard', [DashboardController::class, 'index'])
    ->middleware('auth')
    ->name('admin.dashboard');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';