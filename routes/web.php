<?php
use Illuminate\Support\Facades\Route;

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
    Route::post('/movements/filter/sells', [MovementSellController::class, 'filter'])->name('sells.filter');

    Route::resource('/movements/uses', MovementUseController::class);
    Route::post('/movements/filter/uses', [MovementUseController::class, 'filter'])->name('uses.filter');

    Route::resource('/movements/entries', MovementEntryController::class);
    Route::post('/movements/filter/entries', [MovementEntryController::class, 'filter'])->name('entries.filter');
    Route::post('/movements/delete-item/entries', [MovementEntryController::class, 'delete_item'])->name('entries.delete_item');

    Route::resource('/payments', PaymentController::class);
    Route::put('/payments/pay/{payment}', [PaymentController::class, 'pay'])->where('payment', '[0-9]+')->name('payments.pay');
    Route::get('/previous/payments', [PaymentController::class, 'previous'])->name('payments.previous');

    Route::resource('/services', ServiceController::class);
    Route::put('/services/pay/{service}', [ServiceController::class, 'pay'])->where('service', '[0-9]+')->name('services.pay');
    Route::put('/services/conclude/{service}', [ServiceController::class, 'conclude'])->where('service', '[0-9]+')->name('services.conclude');
    Route::get('/previous/services', [ServiceController::class, 'previous'])->name('services.previous');

    Route::resource('/employees', EmployeeController::class);
    Route::delete('/employees/fire/{id}', [EmployeeController::class, 'fire'])->name('employees.fire');
    Route::get('/previous/employees', [EmployeeController::class, 'previous'])->name('employees.previous');
    Route::get('overtime-calculation/employees/{id?}', [EmployeeController::class, 'overtime_calculation'])->where('id', '[0-9]*')->name('employee.overtime_calculation');
    Route::get('overtime-calculation/list/employees/{date?}', [EmployeeController::class, 'overtime_calculation_list'])->name('employee.overtime_calculation_list');
    Route::post('overtime-calculation/save/employees', [EmployeeController::class, 'overtime_calculation_save'])->name('employee.overtime_calculation_save');

    Route::resource('/payslip', PayslipController::class);
    Route::get('/filter/payslip/{employee?}/{month?}/{year?}', [PayslipController::class, 'index'])
        ->where('employee', '[0-9]*')
        ->where('month', '[0-9]*')
        ->where('year', '[0-9]*')
        ->name('payslip.filter');
    Route::get('/print/payslip/{mode?}/{employee?}/{month?}/{year?}', [PayslipController::class, 'print_payslips'])
        ->where('mode', '[a-z\-A-Z]*')
        ->where('employee', '[0-9]*')
        ->where('month', '[0-9]*')
        ->where('year', '[0-9]*')
        ->name('payslip.print');
    Route::put('/payslip/detail/{payslip}', [PayslipController::class, 'update_detail'])->where('payslip', '[0-9]*')->name('payslip.update_detail');
    Route::put('/payslip/observations/{employee}/{month}/{year}', [PayslipController::class, 'update_observations'])
        ->where('employee', '[0-9]*')
        ->where('month', '[0-9]*')
        ->where('year', '[0-9]*')
        ->name('payslip.observations');
});