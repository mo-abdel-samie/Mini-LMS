<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Schema;
use Modules\Course\Models\Course;
use Modules\Course\Models\Lesson;
use Modules\Enrollment\Models\Enrollment;
use Modules\Progres\Models\CourseCompletion;

class LmsStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalCourses = Schema::hasTable('courses') ? Course::count() : 0;
        $totalUsers = Schema::hasTable('users') ? User::count() : 0;
        $totalLessons = Schema::hasTable('lessons') ? Lesson::count() : 0;

        $totalEnrollments = Schema::hasTable('enrollments') ? Enrollment::count() : 0;
        $completedCourses = Schema::hasTable('course_completions') ? CourseCompletion::count() : 0;
        $avgCompletion = $totalEnrollments > 0
            ? round(($completedCourses / $totalEnrollments) * 100, 1)
            : 0;

        return [
            Stat::make('Total Courses', number_format($totalCourses))
                ->description('Published and draft')
                ->icon('heroicon-o-book-open'),
            Stat::make('Total Users', number_format($totalUsers))
                ->description('Registered learners/admins')
                ->icon('heroicon-o-users'),
            Stat::make('Total Lessons', number_format($totalLessons))
                ->description('Across all courses')
                ->icon('heroicon-o-play-circle'),
            Stat::make('Avg Completion', $avgCompletion . '%')
                ->description('Completed enrollments ratio')
                ->icon('heroicon-o-chart-bar'),
        ];
    }
}
