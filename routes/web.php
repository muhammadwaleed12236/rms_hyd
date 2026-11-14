<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SubcategoryController;
use Illuminate\Support\Facades\Route;

// Protected routes (require authentication)
Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('categories', CategoryController::class);
    Route::resource('subcategories', SubcategoryController::class)->except('index'); // Subcategories managed in categories view
    Route::resource('items', ItemController::class);
    Route::resource('sales', SaleController::class);
    Route::resource('expenses', ExpenseController::class);
    Route::resource('payments', PaymentController::class);

    // POS Route
    Route::get('pos', [SaleController::class, 'create'])->name('pos.index');

    // API Routes
    Route::prefix('api')->group(function () {
        Route::get('categories', function () {
            return response()->json(\App\Models\Category::all());
        });
        Route::get('subcategories', function () {
            return response()->json(\App\Models\Subcategory::with('category')->get());
        });
        Route::post('payments', [PaymentController::class, 'store']);
        Route::get('sales/{sale}', [SaleController::class, 'show']);
        Route::post('sales', [SaleController::class, 'store']);
        Route::post('sales/{sale}/add-payment', [SaleController::class, 'addPayment']);
    });
});

// Auth routes (login, logout etc)
require __DIR__.'/auth.php';
