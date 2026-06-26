<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

/**
 * API Routes
 *
 * All routes prefixed with /api
 * Authentication uses JWT via the 'api' guard
 */

// Public routes (no authentication required)
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
});

// Protected routes (JWT authentication)
Route::middleware('auth:api')->group(function () {

    // Authentication routes
    Route::prefix('auth')->group(function () {
        Route::get('/me', [AuthController::class, 'me'])->name('auth.me');
        Route::post('/refresh', [AuthController::class, 'refresh'])->name('auth.refresh');
        Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
    });

    // Order management routes
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('orders.index');
        Route::post('/', [OrderController::class, 'store'])->name('orders.store');
        Route::get('/{id}', [OrderController::class, 'show'])->name('orders.show');
        Route::put('/{id}', [OrderController::class, 'update'])->name('orders.update');
        Route::delete('/{id}', [OrderController::class, 'destroy'])->name('orders.destroy');
        Route::post('/{id}/confirm', [OrderController::class, 'confirm'])->name('orders.confirm');

        // Payment routes for specific orders
        Route::post('/{id}/payments', [PaymentController::class, 'process'])->name('payments.process');
        Route::get('/{id}/payments', [PaymentController::class, 'indexByOrder'])->name('payments.by-order');
    });

    // Payment management routes
    Route::prefix('payments')->group(function () {
        Route::get('/', [PaymentController::class, 'index'])->name('payments.index');
        Route::get('/methods', [PaymentController::class, 'getAvailableMethods'])->name('payments.methods');
        Route::get('/{id}', [PaymentController::class, 'show'])->name('payments.show');
    });
});
