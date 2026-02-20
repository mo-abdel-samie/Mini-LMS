<?php

namespace Modules\Course\Repositories;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Modules\Course\Models\Course;
use Modules\Course\Models\Lesson;

class EloquentLessonViewRepository implements LessonViewRepositoryInterface
{
    public function hasLessonProgressTable(): bool
    {
        return Schema::hasTable('lesson_progress');
    }

    public function loadCourseLevel(Course $course): Course
    {
        $course->load('level:id,name');

        return $course;
    }

    public function getAccessibleLessons(int $courseId, bool $isEnrolled): Collection
    {
        return Lesson::query()
            ->where('course_id', $courseId)
            ->when(! $isEnrolled, fn ($query) => $query->where('is_free_preview', true))
            ->orderBy('order')
            ->orderBy('id')
            ->get(['id', 'course_id', 'title', 'slug', 'order', 'is_free_preview']);
    }
}

