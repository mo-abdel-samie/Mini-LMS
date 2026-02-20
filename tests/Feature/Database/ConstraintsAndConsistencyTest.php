<?php

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Course\Models\Course;
use Modules\Course\Models\Lesson;
use Modules\Level\Models\Level;
use Modules\Progres\Models\CourseCompletion;
use Modules\Progres\Models\LessonProgress;
use Modules\Progres\Services\ProgressServiceInterface;

uses(RefreshDatabase::class);

it('enforces unique slug database constraint on levels', function (): void {
    Level::create([
        'name' => 'Beginner',
        'slug' => 'beginner',
    ]);

    expect(fn() => Level::create([
        'name' => 'Another Beginner',
        'slug' => 'beginner',
    ]))->toThrow(QueryException::class);
});

it('keeps progress and completion state consistent when lessons are completed incrementally', function (): void {
    $service = app(ProgressServiceInterface::class);
    $user = User::factory()->create();
    [$course, $lessons] = createConsistencyCourseWithLessons(2);

    $service->completeLesson($user->id, $course->id, $lessons[0]->id);

    $this->assertDatabaseHas('lesson_progress', [
        'user_id' => $user->id,
        'course_id' => $course->id,
        'lesson_id' => $lessons[0]->id,
    ]);
    $this->assertDatabaseMissing('course_completions', [
        'user_id' => $user->id,
        'course_id' => $course->id,
    ]);

    $service->completeLesson($user->id, $course->id, $lessons[1]->id);

    expect(LessonProgress::query()
        ->where('user_id', $user->id)
        ->where('course_id', $course->id)
        ->count())->toBe(2);

    expect(CourseCompletion::query()
        ->where('user_id', $user->id)
        ->where('course_id', $course->id)
        ->count())->toBe(1);
});

/**
 * @return array{0: Course, 1: array<int, Lesson>}
 */
function createConsistencyCourseWithLessons(int $lessonCount): array
{
    $level = Level::create([
        'name' => 'Consistency Level ' . str()->random(5),
        'slug' => 'consistency-level-' . str()->lower(str()->random(8)),
    ]);

    $course = Course::create([
        'level_id' => $level->id,
        'title' => 'Consistency Course ' . str()->random(5),
        'slug' => 'consistency-course-' . str()->lower(str()->random(8)),
        'is_published' => true,
    ]);

    $lessons = [];
    for ($i = 1; $i <= $lessonCount; $i++) {
        $lessons[] = Lesson::create([
            'course_id' => $course->id,
            'title' => "Consistency Lesson {$i}",
            'order' => $i,
            'video_url' => "https://example.com/consistency-{$i}",
            'is_free_preview' => true,
        ]);
    }

    return [$course, $lessons];
}
