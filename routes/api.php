<?php

use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\MeController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::middleware('auth:sanctum')->get('/me', [MeController::class, 'show'])->name('me');

    Route::get('catalog', [CatalogController::class, 'index'])->name('catalog.index');
    Route::get('catalog/{slug}', [CatalogController::class, 'show'])->name('catalog.show');
});
