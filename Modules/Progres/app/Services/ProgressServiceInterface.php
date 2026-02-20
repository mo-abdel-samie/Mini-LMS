<?php

namespace Modules\Progres\Services;

use Illuminate\Support\Collection;
use Modules\Progres\Models\CourseCompletion;
use Modules\Progres\Models\LessonProgress;

interface ProgressServiceInterface
{
    public function startLesson(int $userId, int $courseId, int $lessonId): LessonProgress;

    public function updateWatchSeconds(int $userId, int $courseId, int $lessonId, int $watchSeconds): LessonProgress;

    public function completeLesson(int $userId, int $courseId, int $lessonId): LessonProgress;

    public function syncCourseCompletion(int $userId, int $courseId): ?CourseCompletion;

    public function isCourseCompleted(int $userId, int $courseId): bool;

    public function getCompletedLessonIds(int $userId, int $courseId): Collection;
}
