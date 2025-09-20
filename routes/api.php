<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware(['auth:sanctum', 'customer'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        // tambahkan route khusus customer lain di sini
    });
});
