<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\CategoryManagementController;
use App\Http\Controllers\Admin\CurrencyManagementController;
use App\Http\Controllers\Admin\ServicePackageManagementController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [LandingPageController::class, 'landingPage']);

Route::get('/language/{lang}', function ($locale) {
    session(['locale' => $locale]);
    return redirect()->back();

})->name('change.language');

Route::middleware(['auth', 'check.admin'])
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');

        Route::prefix('users')->group(function () {
            Route::get('/', [UserManagementController::class, 'index'])->name('users.index');
            Route::get('/{user}', [UserManagementController::class, 'show'])->name('users.show');
            Route::get('/{user}/edit', [UserManagementController::class, 'edit'])->name('users.edit');
            Route::put('/{user}', [UserManagementController::class, 'update'])->name('users.update');
            Route::delete('/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');
        });

        Route::prefix('categories')->group(function () {
            Route::get('/', [CategoryManagementController::class, 'index'])->name('categories.index');
            Route::get('/create', [CategoryManagementController::class, 'create'])->name('categories.create');
            Route::get('/{category}', [CategoryManagementController::class, 'show'])->name('categories.show');
            Route::post('/', [CategoryManagementController::class, 'store'])->name('categories.store');
            Route::get('/{category}/edit', [CategoryManagementController::class, 'edit'])->name('categories.edit');
            Route::put('/{category}', [CategoryManagementController::class, 'update'])->name('categories.update');
            Route::delete('/{category}', [CategoryManagementController::class, 'destroy'])->name('categories.destroy');
        });

        Route::prefix('currencies')->group(function () {
            Route::get('/', [CurrencyManagementController::class, 'index'])->name('currencies.index');
            Route::post('/update-exchange-rates', [CurrencyManagementController::class, 'updateExchangeRates'])
                ->name('currencies.updateExchangeRates');
            Route::post('/currencies/{currency}/change-currency-default', [CurrencyManagementController::class, 'changeCurrencyDefault'])
                ->name('currencies.changeCurrencyDefault');
        });

        Route::prefix('service-packages')->group(function () {
            Route::get('/', [ServicePackageManagementController::class, 'index'])->name('service-packages.index');
            Route::get('/service-packages/{id}', [ServicePackageManagementController::class, 'show'])
                ->name('service-packages.show');
        });
    });

require __DIR__ . '/auth.php';
