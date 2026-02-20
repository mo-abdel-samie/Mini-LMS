<?php

namespace Modules\Course\Repositories;

use Modules\Course\Models\Course;

interface CourseViewRepositoryInterface
{
    public function loadCourseForShow(Course $course): Course;

    public function hasLessonProgressTable(): bool;

    public function getCompletedLessonsCount(int $userId, int $courseId): int;

    /**
     * @return array<int>
     */
    public function getCompletedLessonIds(int $userId, int $courseId): array;
}

