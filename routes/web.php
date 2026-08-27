<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\SupplierInvoiceController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Redirect root to dashboard (if auth) or login (if guest)
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // POS Kasir
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::post('/pos', [PosController::class, 'store'])->name('pos.store');

    // Products Master Data
    Route::resource('/products', ProductController::class)->except(['create', 'edit', 'show']);

    // Categories
    Route::resource('/categories', CategoryController::class)->except(['create', 'edit', 'show']);

    // Suppliers
    Route::resource('/suppliers', SupplierController::class)->except(['create', 'edit', 'show']);

    // Supplier Invoices / Tagihan Supplier
    Route::resource('/supplier-invoices', SupplierInvoiceController::class)->except(['create', 'edit', 'show']);
    Route::post('/supplier-invoices/{supplierInvoice}/pay', [SupplierInvoiceController::class, 'payInstallment'])->name('supplier-invoices.pay');

    // Customers
    Route::resource('/customers', CustomerController::class)->except(['create', 'edit', 'show']);

    // Stock Movements
    Route::get('/stock', [StockMovementController::class, 'index'])->name('stock.index');
    Route::post('/stock', [StockMovementController::class, 'store'])->name('stock.store');

    // Transactions History
    Route::resource('/transactions', TransactionController::class)->only(['index', 'show', 'update', 'destroy']);

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export-csv', [ReportController::class, 'exportCsv'])->name('reports.export-csv');

    // Staff Users Management
    Route::resource('/users', UserController::class)->except(['create', 'edit', 'show']);
});

require __DIR__.'/settings.php';
