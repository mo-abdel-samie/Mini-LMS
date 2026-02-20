<?php

namespace Modules\Course\Services;

use Modules\Course\Models\Course;
use Modules\Course\Models\Lesson;

interface LessonViewServiceInterface
{
    /**
     * @return array{
     *   course: Course,
     *   lesson: Lesson,
     *   isEnrolled: bool,
     *   previousLesson: Lesson|null,
     *   nextLesson: Lesson|null
     * }
     */
    public function getShowLessonPayload(Course $course, Lesson $lesson, ?int $userId): array;
}

