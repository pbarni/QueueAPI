<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\QueueController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/hello', [QueueController::class, 'index']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/{event}/register', [QueueController::class, 'register']);
    Route::delete('/{event}/register', [QueueController::class, 'unregister']);
});

//default
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
