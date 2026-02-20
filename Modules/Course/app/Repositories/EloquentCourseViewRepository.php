<?php

namespace Modules\Course\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Course\Models\Course;

class EloquentCourseViewRepository implements CourseViewRepositoryInterface
{
    public function loadCourseForShow(Course $course): Course
    {
        $course->load([
            'level:id,name',
            'lessons:id,course_id,title,slug,order,video_url,is_free_preview',
        ])->loadCount(['lessons', 'enrollments', 'completions']);

        return $course;
    }

    public function hasLessonProgressTable(): bool
    {
        return Schema::hasTable('lesson_progress');
    }

    public function getCompletedLessonsCount(int $userId, int $courseId): int
    {
        return (int) DB::table('lesson_progress')
            ->where('user_id', $userId)
            ->where('course_id', $courseId)
            ->whereNotNull('completed_at')
            ->distinct('lesson_id')
            ->count('lesson_id');
    }

    public function getCompletedLessonIds(int $userId, int $courseId): array
    {
        return DB::table('lesson_progress')
            ->where('user_id', $userId)
            ->where('course_id', $courseId)
            ->whereNotNull('completed_at')
            ->pluck('lesson_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }
}

