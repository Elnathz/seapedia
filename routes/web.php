<?php

use App\Http\Controllers\Web\AppReviewController;
use App\Http\Controllers\Web\CatalogController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\RoleController;
use App\Http\Controllers\Web\SellerStoreController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::get('catalog', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('catalog/{product}', [CatalogController::class, 'show'])->name('catalog.show');

Route::get('reviews', [AppReviewController::class, 'index'])->name('reviews.index');
Route::post('reviews', [AppReviewController::class, 'store'])->name('reviews.store');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('role/select', [RoleController::class, 'create'])->name('role.select');
    Route::post('role/select', [RoleController::class, 'store'])->name('role.store');

    Route::middleware('active_role:seller')->prefix('seller')->name('seller.')->group(function () {
        Route::get('store', [SellerStoreController::class, 'show'])->name('store.show');
        Route::post('store', [SellerStoreController::class, 'store'])->name('store.store');
        Route::put('store/{store}', [SellerStoreController::class, 'update'])->name('store.update');
    });
});

require __DIR__.'/settings.php';
