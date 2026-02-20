<?php

namespace Modules\Progres\Repositories;

use Illuminate\Support\Collection;
use Modules\Progres\Models\LessonProgress;

interface LessonProgressRepositoryInterface
{
    public function findByUserAndLesson(int $userId, int $lessonId): ?LessonProgress;

    public function markStarted(int $userId, int $courseId, int $lessonId): LessonProgress;

    public function updateWatchSeconds(int $userId, int $courseId, int $lessonId, int $watchSeconds): LessonProgress;

    public function markCompleted(int $userId, int $courseId, int $lessonId): LessonProgress;

    public function getCompletedLessonIdsByCourse(int $userId, int $courseId): Collection;
}
