<?php

use App\Models\User;
use App\Mail\CourseCompletedMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Modules\Course\Models\Course;
use Modules\Course\Models\Lesson;
use Modules\Level\Models\Level;
use Modules\Progres\Models\CourseCompletion;
use Modules\Progres\Models\LessonProgress;
use Modules\Progres\Services\ProgressServiceInterface;

uses(RefreshDatabase::class);

it('marks lesson progress when a lesson is completed', function (): void {
    $service = app(ProgressServiceInterface::class);
    $user = User::factory()->create();
    [$course, $lessons] = createCourseWithLessons(2);

    $progress = $service->completeLesson($user->id, $course->id, $lessons[0]->id);

    expect($progress)->toBeInstanceOf(LessonProgress::class);
    expect($progress->user_id)->toBe($user->id);
    expect($progress->course_id)->toBe($course->id);
    expect($progress->lesson_id)->toBe($lessons[0]->id);
    expect($progress->completed_at)->not->toBeNull();
    expect(CourseCompletion::count())->toBe(0);
});

it('marks course as completed only when all lessons are completed', function (): void {
    Mail::fake();

    $service = app(ProgressServiceInterface::class);
    $user = User::factory()->create();
    [$course, $lessons] = createCourseWithLessons(2);

    $service->completeLesson($user->id, $course->id, $lessons[0]->id);
    expect($service->isCourseCompleted($user->id, $course->id))->toBeFalse();

    $service->completeLesson($user->id, $course->id, $lessons[1]->id);
    expect($service->isCourseCompleted($user->id, $course->id))->toBeTrue();
    expect(CourseCompletion::count())->toBe(1);

    Mail::assertSent(CourseCompletedMail::class, function (CourseCompletedMail $mail) use ($user, $course): bool {
        return $mail->user->is($user) && $mail->course->is($course);
    });

    $service->completeLesson($user->id, $course->id, $lessons[1]->id);

    Mail::assertSent(CourseCompletedMail::class, 1);
});

it('removes course completion when sync runs and course is no longer fully completed', function (): void {
    $service = app(ProgressServiceInterface::class);
    $user = User::factory()->create();
    [$course, $lessons] = createCourseWithLessons(2);

    $service->completeLesson($user->id, $course->id, $lessons[0]->id);
    $service->completeLesson($user->id, $course->id, $lessons[1]->id);
    expect($service->isCourseCompleted($user->id, $course->id))->toBeTrue();

    LessonProgress::query()
        ->where('user_id', $user->id)
        ->where('lesson_id', $lessons[1]->id)
        ->delete();

    $service->syncCourseCompletion($user->id, $course->id);

    expect($service->isCourseCompleted($user->id, $course->id))->toBeFalse();
    expect(CourseCompletion::count())->toBe(0);
});

it('returns unique completed lesson ids for a user and course', function (): void {
    $service = app(ProgressServiceInterface::class);
    $user = User::factory()->create();
    [$course, $lessons] = createCourseWithLessons(3);

    $service->completeLesson($user->id, $course->id, $lessons[0]->id);
    $service->completeLesson($user->id, $course->id, $lessons[1]->id);
    $service->completeLesson($user->id, $course->id, $lessons[0]->id);

    $ids = $service->getCompletedLessonIds($user->id, $course->id);

    expect($ids)->toHaveCount(2);
    expect($ids->all())->toEqualCanonicalizing([$lessons[0]->id, $lessons[1]->id]);
});

/**
 * @return array{0: Course, 1: array<int, Lesson>}
 */
function createCourseWithLessons(int $lessonCount): array
{
    $level = Level::create([
        'name' => 'Progress Level ' . str()->random(5),
        'slug' => 'progress-level-' . str()->lower(str()->random(8)),
    ]);

    $course = Course::create([
        'level_id' => $level->id,
        'title' => 'Progress Course ' . str()->random(5),
        'slug' => 'progress-course-' . str()->lower(str()->random(8)),
        'is_published' => false,
    ]);

    $lessons = [];
    for ($i = 1; $i <= $lessonCount; $i++) {
        $lessons[] = Lesson::create([
            'course_id' => $course->id,
            'title' => "Lesson {$i}",
            'order' => $i,
            'video_url' => "https://example.com/video-{$i}",
            'is_free_preview' => false,
        ]);
    }

    return [$course, $lessons];
}
