<?php

namespace Modules\Progres\Repositories;

use Illuminate\Support\Carbon;
use Modules\Progres\Models\CourseCompletion;

class EloquentCourseCompletionRepository implements CourseCompletionRepositoryInterface
{
    public function findByUserAndCourse(int $userId, int $courseId): ?CourseCompletion
    {
        return CourseCompletion::query()
            ->where('user_id', $userId)
            ->where('course_id', $courseId)
            ->first();
    }

    public function markCompleted(int $userId, int $courseId): CourseCompletion
    {
        return CourseCompletion::updateOrCreate(
            [
                'user_id' => $userId,
                'course_id' => $courseId,
            ],
            [
                'completed_at' => Carbon::now(),
            ]
        );
    }

    public function removeCompletion(int $userId, int $courseId): bool
    {
        return (bool) CourseCompletion::query()
            ->where('user_id', $userId)
            ->where('course_id', $courseId)
            ->delete();
    }

    public function isCompleted(int $userId, int $courseId): bool
    {
        return $this->findByUserAndCourse($userId, $courseId) !== null;
    }
}
