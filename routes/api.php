<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/ads', [App\Http\Controllers\Api\V1\AdController::class, 'store']);
        Route::get('/my-ads', [App\Http\Controllers\Api\V1\AdController::class, 'index']);
        Route::get('/ads/{id}', [App\Http\Controllers\Api\V1\AdController::class, 'show']);
    });
});
