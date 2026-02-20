<?php

namespace Modules\Course\Repositories;

use Illuminate\Support\Collection;
use Modules\Course\Models\Course;

interface LessonViewRepositoryInterface
{
    public function hasLessonProgressTable(): bool;

    public function loadCourseLevel(Course $course): Course;

    /**
     * @return Collection<int, \Modules\Course\Models\Lesson>
     */
    public function getAccessibleLessons(int $courseId, bool $isEnrolled): Collection;
}

