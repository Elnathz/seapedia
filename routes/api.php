<?php

use App\Http\Controllers\Api\MeController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::middleware('auth:sanctum')->get('/me', [MeController::class, 'show'])->name('me');
});
