<?php

namespace Modules\Course\Services;

use Modules\Course\Models\Course;

interface CourseViewServiceInterface
{
    /**
     * @return array{
     *   course: Course,
     *   isEnrolled: bool,
     *   lessons: \Illuminate\Support\Collection<int, \Modules\Course\Models\Lesson>,
     *   courseProgress: array{completed:int,total:int,percent:int}|null,
     *   completedLessonIds: array<int>
     * }
     */
    public function getShowCoursePayload(Course $course, ?int $userId): array;
}

