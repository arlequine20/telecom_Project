<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CustomerController;
use App\Http\Controllers\API\SimCardController;
use App\Http\Controllers\API\TransactionController;
use App\Http\Controllers\API\WalletController;

// Public routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::get('sim-cards/lookup/by-phone/{phone}', [SimCardController::class, 'lookupByPhone']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // Customers
    Route::get('customers/stats/overview', [CustomerController::class, 'getStats']);
    Route::apiResource('customers', CustomerController::class);

    // SIM Cards
    Route::get('sim-cards', [SimCardController::class, 'index']);
    Route::get('sim-cards/unassigned', [SimCardController::class, 'unassigned']);
    Route::get('sim-cards/stats/overview', [SimCardController::class, 'getStats']);
    Route::get('sim-cards/{id}', [SimCardController::class, 'show']);
    Route::get('sim-cards/{id}/balance', [SimCardController::class, 'getBalance']);
    Route::post('sim-cards', [SimCardController::class, 'store']);
    Route::put('sim-cards/{id}/assign', [SimCardController::class, 'assign']);
    Route::put('sim-cards/{id}/status', [SimCardController::class, 'updateStatus']);

    // Transactions
    Route::get('transactions/stats/overview', [TransactionController::class, 'getStats']);
    Route::apiResource('transactions', TransactionController::class);
    Route::post('transactions/{id}/approve', [TransactionController::class, 'approve']);
    Route::post('transactions/{id}/cancel', [TransactionController::class, 'cancel']);

    // Wallet
    Route::get('wallet', [WalletController::class, 'show']);
    Route::post('wallet/add-balance', [WalletController::class, 'addBalance']);
    Route::post('wallet/deduct-balance', [WalletController::class, 'deductBalance']);
    Route::post('wallet/add-data', [WalletController::class, 'addDataBalance']);
    Route::get('wallet/stats', [WalletController::class, 'getStats']);
});
