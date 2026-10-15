<?php

use App\Http\Controllers\Auth\SchoolPasswordResetController;
use Illuminate\Support\Facades\Route;

Route::middleware(['guest'])->group(function () {
    Route::get('forgot-password', [SchoolPasswordResetController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('forgot-password', [SchoolPasswordResetController::class, 'sendResetLink'])->name('password.email');

    Route::get('reset-password/{token}', [SchoolPasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('reset-password', [SchoolPasswordResetController::class, 'reset'])->name('password.update');
});
