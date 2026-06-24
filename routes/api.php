<?php

use App\Http\Controllers\Api\Admin\PromoController;
use App\Http\Controllers\Api\Admin\VoucherController;
use App\Http\Controllers\Api\BuyerAddressController;
use App\Http\Controllers\Api\BuyerCartController;
use App\Http\Controllers\Api\BuyerOrderController;
use App\Http\Controllers\Api\BuyerWalletController;
use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\MeController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::middleware('auth:sanctum')->get('/me', [MeController::class, 'show'])->name('me');

    Route::get('catalog', [CatalogController::class, 'index'])->name('catalog.index');
    Route::get('catalog/{slug}', [CatalogController::class, 'show'])->name('catalog.show');

    Route::middleware(['auth:sanctum', 'active_role:buyer'])->prefix('buyer')->name('buyer.')->group(function () {
        Route::get('wallet', [BuyerWalletController::class, 'show'])->name('wallet.show');
        Route::post('wallet/topup', [BuyerWalletController::class, 'store'])->name('wallet.topup');

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
    });

    Route::middleware(['auth:sanctum', 'is_admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('promos', [PromoController::class, 'index'])->name('promos.index');
        Route::post('promos', [PromoController::class, 'store'])->name('promos.store');
        Route::get('promos/{promo}', [PromoController::class, 'show'])->name('promos.show');

        Route::get('vouchers', [VoucherController::class, 'index'])->name('vouchers.index');
        Route::post('vouchers', [VoucherController::class, 'store'])->name('vouchers.store');
        Route::get('vouchers/{voucher}', [VoucherController::class, 'show'])->name('vouchers.show');
    });
});
