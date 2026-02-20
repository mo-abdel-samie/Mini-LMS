<?php

namespace Modules\Progres\Repositories;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Modules\Progres\Models\LessonProgress;

class EloquentLessonProgressRepository implements LessonProgressRepositoryInterface
{
    public function findByUserAndLesson(int $userId, int $lessonId): ?LessonProgress
    {
        return LessonProgress::query()
            ->where('user_id', $userId)
            ->where('lesson_id', $lessonId)
            ->first();
    }

    public function markStarted(int $userId, int $courseId, int $lessonId): LessonProgress
    {
        $progress = LessonProgress::firstOrNew([
            'user_id' => $userId,
            'lesson_id' => $lessonId,
        ]);

        $progress->course_id = $courseId;
        $progress->started_at ??= Carbon::now();
        $progress->watch_seconds ??= 0;
        $progress->save();

        return $progress;
    }

    public function updateWatchSeconds(int $userId, int $courseId, int $lessonId, int $watchSeconds): LessonProgress
    {
        $progress = $this->markStarted($userId, $courseId, $lessonId);
        $watchSeconds = max(0, $watchSeconds);

        if ($watchSeconds > (int) $progress->watch_seconds) {
            $progress->watch_seconds = $watchSeconds;
            $progress->save();
        }

        return $progress;
    }

    public function markCompleted(int $userId, int $courseId, int $lessonId): LessonProgress
    {
        $progress = $this->markStarted($userId, $courseId, $lessonId);
        $progress->completed_at ??= Carbon::now();
        $progress->save();

        return $progress;
    }

    public function getCompletedLessonIdsByCourse(int $userId, int $courseId): Collection
    {
        return LessonProgress::query()
            ->where('user_id', $userId)
            ->where('course_id', $courseId)
            ->whereNotNull('completed_at')
            ->pluck('lesson_id');
    }
}
