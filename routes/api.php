<?php

use App\Http\Controllers\AuthController;
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

