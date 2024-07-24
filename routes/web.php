<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\MovementEntryController;
use App\Http\Controllers\MovementUseController;
use App\Http\Controllers\MovementSellController;
use App\Http\Controllers\RecordController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\PayslipController;

require_once 'fortify.php';

/*
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});
*/

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('home');
    Route::resource('/dashboard', DashboardController::class);

    Route::get('/warehouse', [ItemController::class, 'index'])->name('warehouse.index');
    Route::post('/warehouse', [ItemController::class, 'store'])->name('warehouse.store');
    Route::delete('/warehouse/{werehouse}', [ItemController::class, 'destroy'])->where('wherehouse', '[0-9]+')->name('warehouse.destroy');
    Route::post('/warehouse/{werehouse}', [ItemController::class, 'update'])->where('warehouse', '[0-9]+')->name('warehouse.update');
    Route::get('/warehouse/filter/{werehouse?}', [ItemController::class, 'index'])->where('wherehouse', '[0-9]+')->name('warehouse.filter');

    Route::get('/print-items/{buy_option?}', [ItemController::class, 'print_items'])->name('warehouse.print_items');
    Route::get('/use-table/{month?}', [ItemController::class, 'use_table'])->name('warehouse.use_table');

    Route::resource('/records', RecordController::class);

    Route::resource('/movements/sells', MovementSellController::class);
    Route::resource('/movements/uses', MovementUseController::class);
    Route::resource('/movements/entries', MovementEntryController::class);

    Route::resource('/payments', PaymentController::class);
    Route::put('/payments/pay/{payment}', [PaymentController::class, 'pay'])->where('payment', '[0-9]+')->name('payments.pay');
    Route::get('/previous/payments', [PaymentController::class, 'previous'])->name('payments.previous');

    Route::resource('/services', ServiceController::class);
    Route::put('/services/pay/{service}', [ServiceController::class, 'pay'])->where('service', '[0-9]+')->name('services.pay');
    Route::put('/services/conclude/{service}', [ServiceController::class, 'conclude'])->where('service', '[0-9]+')->name('services.conclude');
    Route::get('/previous/services', [ServiceController::class, 'previous'])->name('services.previous');

    Route::resource('/employees', EmployeeController::class);
    Route::post('/employees/fire', [EmployeeController::class, 'fire'])->name('employees.fire');
    Route::resource('/payslips', PayslipController::class);
});