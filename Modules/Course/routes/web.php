<?php

use Illuminate\Support\Facades\Route;
use Modules\Course\Actions\EnrollInCourseAction;
use Modules\Course\Actions\ShowCourseAction;
use Modules\Course\Actions\ShowLessonAction;
use Modules\Course\Actions\TrackLessonProgressAction;

Route::group(['prefix' => 'courses', 'as' => 'courses.'], function () {
    Route::get('/{course:slug}', ShowCourseAction::class)->name('show');
    Route::get('/{course:slug}/lessons/{lesson:slug}', ShowLessonAction::class)
        ->name('lessons.show');

    Route::group(['middleware' => ['auth', 'verified']], function () {
        Route::post('/{course:slug}/enroll', EnrollInCourseAction::class)
            ->name('enroll');

        Route::post('/{course:slug}/lessons/{lesson:slug}/progress', TrackLessonProgressAction::class)
            ->name('lessons.progress');
    });
});
