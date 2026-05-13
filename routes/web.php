<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GeneratorController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('dashboard'));

Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Generator
    Route::get('/generate', [GeneratorController::class, 'index'])->name('generator.index');
    Route::post('/generate', [GeneratorController::class, 'generate'])->name('generator.generate');

    // Products — export/import must be before resource() to avoid route conflicts
    Route::get('/products/export/csv', [ProductController::class, 'exportCsv'])->name('products.export');
    Route::post('/products/import/csv', [ProductController::class, 'importCsv'])->name('products.import');
    Route::resource('products', ProductController::class);

    // Stock Movements
    Route::get('/movements', [StockMovementController::class, 'index'])->name('movements.index');
    Route::post('/movements', [StockMovementController::class, 'store'])->name('movements.store');
    Route::delete('/movements/{movement}', [StockMovementController::class, 'destroy'])->name('movements.destroy');

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/low-stock', [ReportController::class, 'lowStock'])->name('low-stock');
        Route::get('/expiry', [ReportController::class, 'expiry'])->name('expiry');
        Route::get('/stock-value', [ReportController::class, 'stockValue'])->name('stock-value');
        Route::get('/movements', [ReportController::class, 'movements'])->name('movements');
    });

    // Categories
    Route::resource('categories', CategoryController::class)->except(['show', 'create', 'edit']);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
