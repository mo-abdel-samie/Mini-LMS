<?php

use Illuminate\Support\Facades\Route;
use Modules\Level\Http\Controllers\LevelController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('levels', LevelController::class)->names('level');
});
