<?php

use Illuminate\Support\Facades\Route;
use Modules\Progres\Http\Controllers\ProgresController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('progres', ProgresController::class)->names('progres');
});
