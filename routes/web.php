<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

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

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard')->middleware('auth');

/*
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    
    Route::get('/warehouse', function () {
        return view('pages.warehouse');
    })->name('warehouse');
    
    Route::get('/movements', function () {
        return view('pages.movements.movements');
    })->name('movements');
    
    Route::get('/services', function () {
        return view('pages.services');
    })->name('services');
    
    Route::get('/payments', function () {
        return view('pages.payments');
    })->name('payments');
    
    Route::get('/employees', function () {
        return view('pages.employees');
    })->name('employees');
    
    Route::get('/configurations', function () {
        return view('pages.configurations');
    })->name('configurations');
});
*/

require __DIR__.'/auth.php';
