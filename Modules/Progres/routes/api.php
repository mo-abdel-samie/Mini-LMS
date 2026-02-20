<?php

use Illuminate\Support\Facades\Route;
use Modules\Progres\Http\Controllers\ProgresController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('progres', ProgresController::class)->names('progres');
});
