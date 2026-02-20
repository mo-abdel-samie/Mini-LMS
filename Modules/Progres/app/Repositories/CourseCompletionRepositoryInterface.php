<?php

namespace Modules\Progres\Repositories;

use Modules\Progres\Models\CourseCompletion;

interface CourseCompletionRepositoryInterface
{
    public function findByUserAndCourse(int $userId, int $courseId): ?CourseCompletion;

    public function markCompleted(int $userId, int $courseId): CourseCompletion;

    public function removeCompletion(int $userId, int $courseId): bool;

    public function isCompleted(int $userId, int $courseId): bool;
}

