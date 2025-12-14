<?php

use App\Http\Controllers\Api\V1\AdController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/ads', [AdController::class, 'store']);
        Route::get('/my-ads', [AdController::class, 'index']);
        Route::get('/ads/{id}', [AdController::class, 'show']);
    });
});
