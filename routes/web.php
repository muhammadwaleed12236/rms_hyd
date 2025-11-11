<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SubcategoryController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\PaymentController;

// Protected routes (require authentication)
Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('categories', CategoryController::class);
    Route::resource('subcategories', SubcategoryController::class)->except('index'); // Subcategories managed in categories view
    Route::resource('items', ItemController::class);
    Route::resource('sales', SaleController::class);
    Route::resource('expenses', ExpenseController::class);
    Route::resource('payments', PaymentController::class);

    // API Routes
    Route::prefix('api')->group(function () {
        Route::get('categories', function () {
            return response()->json(\App\Models\Category::all());
        });
        Route::get('subcategories', function () {
            return response()->json(\App\Models\Subcategory::with('category')->get());
        });
        Route::post('payments', [PaymentController::class, 'store']);
        Route::get('sales/{sale}', [PaymentController::class, 'show']);
    });
});

// Auth routes (login, logout etc)
require __DIR__.'/auth.php';
