<?php

use Illuminate\Support\Facades\Route;
use Modules\Level\Http\Controllers\LevelController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('levels', LevelController::class)->names('level');
});
