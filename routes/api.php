<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\DeviceTokenController;


//users endpoints
Route::prefix('v1/auth')->group(function () {
        
        Route::post('/register', [UserController::class, 'register'])->name('register');
        Route::post('/login', [UserController::class,'login'])->name('login');
        
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [UserController::class,'logout'])->name('logout');
            Route::get('/me', [UserController::class,'currentUser'])->name('currentUser');
        });
        
});

//orders endpoints

Route::prefix('v1/orders')->middleware('auth:sanctum')->group(function () {
    
    Route::get('/', [OrderController::class, 'index'])->middleware('ability:orders:read')->name('orders.index');
        
    Route::post('/', [OrderController::class, 'store'])->middleware('ability:orders:write')->name('orders.store');
        
    Route::get('/{id}', [OrderController::class, 'show'])->middleware('ability:orders:read')->name('orders.show');
        
    Route::patch('/{id}', [OrderController::class, 'updateStatus'])->middleware('ability:orders:write') ->name('orders.update');
});

Route::middleware('auth:sanctum')->prefix('v1')->group(function () {

    Route::post('orders/{order}/notify', [OrderController::class, 'notify'])->middleware('ability:notify:send');    
    // Devices routes
    Route::post('devices', [DeviceTokenController::class, 'store'])->middleware('ability:devices:write');
    Route::delete('devices/{device}', [DeviceTokenController::class, 'destroy'])->middleware('ability:devices:write');
});