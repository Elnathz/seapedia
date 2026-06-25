<?php

use App\Http\Controllers\Api\Admin\ClockController as AdminClockController;
use App\Http\Controllers\Api\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Api\Admin\PromoController;
use App\Http\Controllers\Api\Admin\VoucherController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BuyerAddressController;
use App\Http\Controllers\Api\BuyerCartController;
use App\Http\Controllers\Api\BuyerOrderController;
use App\Http\Controllers\Api\BuyerReportController;
use App\Http\Controllers\Api\BuyerWalletController;
use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\Driver\JobController as DriverJobController;
use App\Http\Controllers\Api\MeController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\Seller\OrderController as SellerOrderController;
use App\Http\Controllers\Api\SellerReportController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::post('login', [AuthController::class, 'store'])
        ->middleware('throttle:login')
        ->name('login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'destroy'])->name('logout');
        Route::post('role/select', [RoleController::class, 'store'])->name('role.select');
        Route::get('/me', [MeController::class, 'show'])->name('me');
    });

    Route::get('catalog', [CatalogController::class, 'index'])->name('catalog.index');
    Route::get('catalog/{slug}', [CatalogController::class, 'show'])->name('catalog.show');

    Route::middleware(['auth:sanctum', 'active_role:buyer'])->prefix('buyer')->name('buyer.')->group(function () {
        Route::get('wallet', [BuyerWalletController::class, 'show'])->name('wallet.show');
        Route::post('wallet/topup', [BuyerWalletController::class, 'store'])->name('wallet.topup');
        Route::get('wallet/topup/{topup}', [BuyerWalletController::class, 'topup'])->name('wallet.topup.show');

        Route::get('addresses', [BuyerAddressController::class, 'index'])->name('addresses.index');
        Route::post('addresses', [BuyerAddressController::class, 'store'])->name('addresses.store');
        Route::put('addresses/{address}', [BuyerAddressController::class, 'update'])->name('addresses.update');
        Route::patch('addresses/{address}/default', [BuyerAddressController::class, 'setDefault'])->name('addresses.setDefault');
        Route::delete('addresses/{address}', [BuyerAddressController::class, 'destroy'])->name('addresses.destroy');

        Route::get('cart', [BuyerCartController::class, 'show'])->name('cart.show');
        Route::post('cart/items', [BuyerCartController::class, 'store'])->name('cart.items.store');
        Route::put('cart/items/{item}', [BuyerCartController::class, 'update'])->name('cart.items.update');
        Route::delete('cart/items/{item}', [BuyerCartController::class, 'destroy'])->name('cart.items.destroy');
        Route::post('cart/clear', [BuyerCartController::class, 'clear'])->name('cart.clear');

        Route::post('checkout/preview', [CheckoutController::class, 'preview'])->name('checkout.preview');
        Route::post('checkout', [CheckoutController::class, 'store'])->name('checkout.store');

        Route::get('orders', [BuyerOrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [BuyerOrderController::class, 'show'])->name('orders.show');

        Route::get('reports', [BuyerReportController::class, 'index'])->name('reports.index');
    });

    Route::middleware(['auth:sanctum', 'active_role:seller'])->prefix('seller')->name('seller.')->group(function () {
        Route::post('orders/{order}/process', [SellerOrderController::class, 'process'])->name('orders.process');

        Route::get('reports', [SellerReportController::class, 'index'])->name('reports.index');
    });

    Route::middleware(['auth:sanctum', 'active_role:driver'])->prefix('driver')->name('driver.')->group(function () {
        Route::get('jobs', [DriverJobController::class, 'index'])->name('jobs.index');
        Route::get('jobs/{delivery}', [DriverJobController::class, 'show'])->name('jobs.show');
        Route::post('jobs/{delivery}/take', [DriverJobController::class, 'take'])->name('jobs.take');
        Route::post('jobs/{delivery}/complete', [DriverJobController::class, 'complete'])->name('jobs.complete');
    });

    Route::middleware(['auth:sanctum', 'is_admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::post('clock/advance', [AdminClockController::class, 'advance'])->name('clock.advance');

        Route::get('promos', [PromoController::class, 'index'])->name('promos.index');
        Route::post('promos', [PromoController::class, 'store'])->name('promos.store');
        Route::get('promos/{promo}', [PromoController::class, 'show'])->name('promos.show');

        Route::get('vouchers', [VoucherController::class, 'index'])->name('vouchers.index');
        Route::post('vouchers', [VoucherController::class, 'store'])->name('vouchers.store');
        Route::get('vouchers/{voucher}', [VoucherController::class, 'show'])->name('vouchers.show');
    });
});
