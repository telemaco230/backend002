<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ArticuloController;
use App\Http\Controllers\ProveedorController;
use Illuminate\Support\Facades\Route;

Route::name("api.auth.")->prefix("auth")->group(function () {

    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');

    Route::middleware('jwt')->group(function () {
        Route::get('/user', [AuthController::class, 'getUser'])->name('get');
        Route::put('/user', [AuthController::class, 'updateUser'])->name('update');;
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');;
    });

});

// Rutas para el módulo de artículos
Route::middleware('jwt')->prefix('articulos')->name('api.articulos.')->group(function () {
    Route::get('/', [ArticuloController::class, 'index'])->name('index');
    Route::post('/', [ArticuloController::class, 'store'])->name('store');
    Route::get('/{id}', [ArticuloController::class, 'show'])->name('show');
    Route::post('/{id}', [ArticuloController::class, 'update'])->name('update'); // POST para soportar multipart/form-data
    Route::delete('/{id}', [ArticuloController::class, 'destroy'])->name('destroy');
});

// Rutas para el módulo de proveedores
Route::middleware('jwt')->prefix('proveedores')->name('api.proveedores.')->group(function () {
    Route::get('/', [ProveedorController::class, 'index'])->name('index');
    Route::post('/', [ProveedorController::class, 'store'])->name('store');
    Route::get('/{id}', [ProveedorController::class, 'show'])->name('show');
    Route::put('/{id}', [ProveedorController::class, 'update'])->name('update');
    Route::delete('/{id}', [ProveedorController::class, 'destroy'])->name('destroy');
    Route::get('/{id}/disponibilidad', [ProveedorController::class, 'verificarDisponibilidad'])->name('disponibilidad');
});

