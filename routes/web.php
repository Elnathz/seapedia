<?php

use App\Http\Controllers\Web\AppReviewController;
use App\Http\Controllers\Web\CatalogController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\RoleController;
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
});

require __DIR__.'/settings.php';
