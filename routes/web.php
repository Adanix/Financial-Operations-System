<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocsController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TaxController;
use Illuminate\Support\Facades\Route;

// Guest Authentication
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/demo-login', [AuthController::class, 'demoLogin'])->name('demo.login');
});

// Documentation (accessible to all)
Route::get('/docs', [DocsController::class, 'index'])->name('docs');

// Authenticated Application Shell
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Customers (Read for all, Write for Owner)
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::middleware('owner')->group(function () {
        Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
        Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
        Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit')->whereNumber('customer');
        Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update')->whereNumber('customer');
        Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy')->whereNumber('customer');
    });
    Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show')->whereNumber('customer');

    // Invoices (Read for all, Write for Owner)
    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::middleware('owner')->group(function () {
        Route::get('/invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
        Route::post('/invoices', [InvoiceController::class, 'store'])->name('invoices.store');
        Route::get('/invoices/{invoice}/edit', [InvoiceController::class, 'edit'])->name('invoices.edit')->whereNumber('invoice');
        Route::put('/invoices/{invoice}', [InvoiceController::class, 'update'])->name('invoices.update')->whereNumber('invoice');
        Route::delete('/invoices/{invoice}', [InvoiceController::class, 'destroy'])->name('invoices.destroy')->whereNumber('invoice');
        Route::post('/invoices/{invoice}/issue', [InvoiceController::class, 'issue'])->name('invoices.issue')->whereNumber('invoice');
        Route::post('/invoices/{invoice}/void', [InvoiceController::class, 'void'])->name('invoices.void')->whereNumber('invoice');
        Route::post('/invoices/{invoice}/email', [InvoiceController::class, 'email'])->name('invoices.email')->whereNumber('invoice');
    });
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show')->whereNumber('invoice');
    Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf')->whereNumber('invoice');
    Route::get('/invoices/{invoice}/preview', [InvoiceController::class, 'previewPdf'])->name('invoices.preview')->whereNumber('invoice');

    // Payments (Read for all, Write for Owner)
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::middleware('owner')->group(function () {
        Route::post('/invoices/{invoice}/payments', [PaymentController::class, 'store'])->name('payments.store')->whereNumber('invoice');
        Route::post('/payments/{payment}/reverse', [PaymentController::class, 'reverse'])->name('payments.reverse')->whereNumber('payment');
    });

    // Expenses (Read for all, Write for Owner)
    Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
    Route::middleware('owner')->group(function () {
        Route::get('/expenses/create', [ExpenseController::class, 'create'])->name('expenses.create');
        Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
        Route::get('/expenses/{expense}/edit', [ExpenseController::class, 'edit'])->name('expenses.edit')->whereNumber('expense');
        Route::put('/expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update')->whereNumber('expense');
        Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy')->whereNumber('expense');
    });
    Route::get('/expenses/{expense}', [ExpenseController::class, 'show'])->name('expenses.show')->whereNumber('expense');
    Route::get('/expenses/{expense}/receipt', [ExpenseController::class, 'receipt'])->name('expenses.receipt')->whereNumber('expense');

    // Taxes (Read for all, Write for Owner)
    Route::get('/taxes', [TaxController::class, 'index'])->name('taxes.index');
    Route::middleware('owner')->group(function () {
        Route::post('/taxes', [TaxController::class, 'store'])->name('taxes.store');
        Route::put('/taxes/{taxRate}', [TaxController::class, 'update'])->name('taxes.update')->whereNumber('taxRate');
        Route::patch('/taxes/{taxRate}/default', [TaxController::class, 'setDefault'])->name('taxes.default')->whereNumber('taxRate');
        Route::patch('/taxes/{taxRate}/status', [TaxController::class, 'toggleStatus'])->name('taxes.status')->whereNumber('taxRate');
    });

    // Reports (Read for all)
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');

    // Settings (Read for all, Write for Owner)
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::middleware('owner')->group(function () {
        Route::put('/settings/{section}', [SettingController::class, 'update'])->name('settings.update');
        Route::post('/settings/categories', [SettingController::class, 'storeCategory'])->name('settings.categories.store');
        Route::patch('/settings/categories/{category}', [SettingController::class, 'updateCategory'])->name('settings.categories.update')->whereNumber('category');
    });
});
