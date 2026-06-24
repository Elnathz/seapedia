<?php

use App\Http\Controllers\Web\AppReviewController;
use App\Http\Controllers\Web\BuyerWalletController;
use App\Http\Controllers\Web\CatalogController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\LocaleController;
use App\Http\Controllers\Web\RoleController;
use App\Http\Controllers\Web\SellerProductController;
use App\Http\Controllers\Web\SellerStoreController;
use App\Http\Controllers\Web\StoreController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::post('locale', [LocaleController::class, 'update'])->name('locale.update');

Route::get('catalog', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('catalog/{product}', [CatalogController::class, 'show'])->name('catalog.show');

Route::get('stores/{store}', [StoreController::class, 'show'])->name('stores.show');

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

        Route::get('products', [SellerProductController::class, 'index'])->name('products.index');
        Route::get('products/create', [SellerProductController::class, 'create'])->name('products.create');
        Route::post('products', [SellerProductController::class, 'store'])->name('products.store');
        Route::get('products/{product}/edit', [SellerProductController::class, 'edit'])->name('products.edit');
        Route::put('products/{product}', [SellerProductController::class, 'update'])->name('products.update');
        Route::delete('products/{product}', [SellerProductController::class, 'destroy'])->name('products.destroy');
    });

    Route::middleware('active_role:buyer')->prefix('buyer')->name('buyer.')->group(function () {
        Route::get('wallet', [BuyerWalletController::class, 'show'])->name('wallet.show');
        Route::post('wallet/topup', [BuyerWalletController::class, 'store'])->name('wallet.topup');
    });
});

require __DIR__.'/settings.php';
