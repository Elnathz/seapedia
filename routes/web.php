<?php

use App\Http\Controllers\Web\Admin\ClockController as AdminClockController;
use App\Http\Controllers\Web\Admin\PromoController as AdminPromoController;
use App\Http\Controllers\Web\Admin\VoucherController as AdminVoucherController;
use App\Http\Controllers\Web\AppReviewController;
use App\Http\Controllers\Web\BuyerAddressController;
use App\Http\Controllers\Web\BuyerCartController;
use App\Http\Controllers\Web\BuyerOrderController;
use App\Http\Controllers\Web\BuyerReportController;
use App\Http\Controllers\Web\BuyerWalletController;
use App\Http\Controllers\Web\CatalogController;
use App\Http\Controllers\Web\CheckoutController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\DriverJobController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\LocaleController;
use App\Http\Controllers\Web\RoleController;
use App\Http\Controllers\Web\SellerOrderController;
use App\Http\Controllers\Web\SellerProductController;
use App\Http\Controllers\Web\SellerReportController;
use App\Http\Controllers\Web\SellerStoreController;
use App\Http\Controllers\Web\StoreController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

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

        Route::get('orders', [SellerOrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [SellerOrderController::class, 'show'])->name('orders.show');
        Route::post('orders/{order}/process', [SellerOrderController::class, 'process'])->name('orders.process');

        Route::get('reports', [SellerReportController::class, 'index'])->name('reports.index');
    });

    Route::middleware('active_role:buyer')->prefix('buyer')->name('buyer.')->group(function () {
        Route::get('wallet', [BuyerWalletController::class, 'show'])->name('wallet.show');
        Route::post('wallet/topup', [BuyerWalletController::class, 'store'])->name('wallet.topup');
        Route::get('wallet/topup/{topup}', [BuyerWalletController::class, 'topup'])->name('wallet.topup.show');

        Route::get('addresses', [BuyerAddressController::class, 'index'])->name('addresses.index');
        Route::post('addresses', [BuyerAddressController::class, 'store'])->name('addresses.store');
        Route::put('addresses/{address}', [BuyerAddressController::class, 'update'])->name('addresses.update');
        Route::patch('addresses/{address}/default', [BuyerAddressController::class, 'setDefault'])->name('addresses.setDefault');
        Route::delete('addresses/{address}', [BuyerAddressController::class, 'destroy'])->name('addresses.destroy');

        Route::get('cart', [BuyerCartController::class, 'index'])->name('cart.index');
        Route::post('cart', [BuyerCartController::class, 'store'])->name('cart.store');
        Route::put('cart/{item}', [BuyerCartController::class, 'update'])->name('cart.update');
        Route::delete('cart/{item}', [BuyerCartController::class, 'destroy'])->name('cart.destroy');
        Route::delete('cart', [BuyerCartController::class, 'clear'])->name('cart.clear');

        Route::get('checkout', [CheckoutController::class, 'show'])->name('checkout.show');
        Route::post('checkout', [CheckoutController::class, 'store'])->name('checkout.store');

        Route::get('orders', [BuyerOrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [BuyerOrderController::class, 'show'])->name('orders.show');

        Route::get('reports', [BuyerReportController::class, 'index'])->name('reports.index');
    });

    Route::middleware('active_role:driver')->prefix('driver')->name('driver.')->group(function () {
        Route::get('jobs', [DriverJobController::class, 'index'])->name('jobs.index');
        Route::get('jobs/{delivery}', [DriverJobController::class, 'show'])->name('jobs.show');
        Route::post('jobs/{delivery}/take', [DriverJobController::class, 'take'])->name('jobs.take');
        Route::post('jobs/{delivery}/complete', [DriverJobController::class, 'complete'])->name('jobs.complete');
    });

    Route::middleware('is_admin')->prefix('admin')->name('admin.')->group(function () {
        Route::post('clock/advance', [AdminClockController::class, 'advance'])->name('clock.advance');

        Route::get('promos', [AdminPromoController::class, 'index'])->name('promos.index');
        Route::post('promos', [AdminPromoController::class, 'store'])->name('promos.store');
        Route::get('promos/{promo}', [AdminPromoController::class, 'show'])->name('promos.show');
        Route::patch('promos/{promo}/toggle-active', [AdminPromoController::class, 'toggleActive'])->name('promos.toggleActive');

        Route::get('vouchers', [AdminVoucherController::class, 'index'])->name('vouchers.index');
        Route::post('vouchers', [AdminVoucherController::class, 'store'])->name('vouchers.store');
        Route::get('vouchers/{voucher}', [AdminVoucherController::class, 'show'])->name('vouchers.show');
        Route::patch('vouchers/{voucher}/toggle-active', [AdminVoucherController::class, 'toggleActive'])->name('vouchers.toggleActive');
    });
});

require __DIR__.'/settings.php';
