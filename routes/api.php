<?php

use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\User\WalletController;
use App\Http\Controllers\User\BudgetController;
use App\Models\Role;

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
        });
    });
