<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\CashflowController;
use App\Http\Controllers\TransactionController;

// Home
Route::get('/', [InventoryController::class, 'index'])->name('home');

// Inventario
Route::get('/inventory/add', [InventoryController::class, 'create'])->name('inventory.create');
Route::post('/inventory/add', [InventoryController::class, 'store'])->name('inventory.store');

// Cashflow
Route::get('/cashflow', [CashflowController::class, 'index'])->name('cashflow.index');
Route::get('/cashflow/create', [CashflowController::class, 'create'])->name('cashflow.create');
Route::post('/cashflow', [CashflowController::class, 'store'])->name('cashflow.store');

// Compras
Route::get('/buy', [PurchaseController::class, 'create'])->name('buy.create');
Route::post('/buy', [PurchaseController::class, 'store'])->name('buy.store');

Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
Route::get('/inventory/data', [InventoryController::class, 'data'])->name('inventory.data');

// Ventas
Route::get('/sell', [SaleController::class, 'create'])->name('sell.create');
Route::post('/sell', [SaleController::class, 'sell'])->name('sell.store');

// Historial
Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');

// Actualizar precios (manual)
Route::get('/prices/update', function() {
    $service = new \App\Services\PriceService();
    $results = $service->fetchAndStore();
    return redirect()->route('inventory.index')->with('success', 'Precios actualizados');
})->name('prices.update');