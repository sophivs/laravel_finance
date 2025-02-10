<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TransactionController;


// Rotas de autenticação
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

// Rotas protegidas (Autenticadas)
Route::middleware(['auth:sanctum'])->group(function () {
    // Rotas de transação
    Route::prefix('transactions')->group(function () {
        Route::post('/transfer', [TransactionController::class, 'transfer']);
        Route::post('/deposit', [TransactionController::class, 'deposit']);
    });

    // Rotas de contas
    Route::prefix('accounts')->group(function () {
        Route::get('/', [AccountController::class, 'index']);
        Route::get('/{id}', [AccountController::class, 'show']);
        Route::post('/', [AccountController::class, 'store']);
        Route::put('/{id}', [AccountController::class, 'update']);
        Route::delete('/{id}', [AccountController::class, 'destroy']);
    });
});