<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\User\WalletController;
use App\Http\Controllers\User\BudgetController;
use App\Http\Controllers\User\TransactionController;
use App\Http\Controllers\User\PaymentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/login', [AuthController::class, 'login']);
Route::post('/refresh', [AuthController::class, 'refreshToken']);

Route::middleware(['auth:sanctum, checkAccessTokenExpiry'])
    ->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/profile', [AuthController::class, 'profile']);

        Route::prefix('wallet')->group(function () {
            Route::post('/create', [WalletController::class, 'create']);
            Route::get('/by-user', [WalletController::class, 'getWalletsByUser']);
            Route::get('/get/{id}', [WalletController::class, 'getWalletDetail']);
            Route::put('/update/{id}', [WalletController::class, 'update']);
        });

        Route::prefix('budget')->group(function () {
            Route::post('/create', [BudgetController::class, 'create']);
            Route::get('/get', [BudgetController::class,'getBudgetByUser']);
            Route::get('/get/{id}', [BudgetController::class, 'getDetailById']);
            Route::put('/update/{id}', [BudgetController::class, 'update']);
        });

        Route::prefix('transaction')->group(function () {
            Route::post('/create', [TransactionController::class, 'create']);
            Route::get('/get', [TransactionController::class, 'getTransactionsByUser']);
            Route::get('/get/{id}', [TransactionController::class, 'getTransactionById']);
        });

        Route::prefix('payment')->group(function () {
            Route::post('/momo', [PaymentController::class, 'payWithMomo'])->middleware('throttle:10,1');
            Route::get('/momo/redirect', [PaymentController::class, 'momoRedirect'])->name('payment.momo.redirect');
        });
    });

Route::post('/payment/momo/ipn', [PaymentController::class, 'momoIpn'])
    ->middleware('throttle:10,1')
    ->name('payment.momo.ipn');
