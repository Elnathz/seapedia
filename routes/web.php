<?php

use App\Http\Controllers\Web\AppReviewController;
use App\Http\Controllers\Web\RoleController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::get('reviews', [AppReviewController::class, 'index'])->name('reviews.index');
Route::post('reviews', [AppReviewController::class, 'store'])->name('reviews.store');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::get('role/select', [RoleController::class, 'create'])->name('role.select');
    Route::post('role/select', [RoleController::class, 'store'])->name('role.store');
});

require __DIR__.'/settings.php';
