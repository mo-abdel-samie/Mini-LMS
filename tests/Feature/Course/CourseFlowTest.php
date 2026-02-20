<?php

use App\Mail\CourseCompletedMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Modules\Course\Models\Course;
use Modules\Course\Models\Lesson;
use Modules\Enrollment\Models\Enrollment;
use Modules\Level\Models\Level;
use Modules\Progres\Models\LessonProgress;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

it('requires login for enrollment', function (): void {
    [$course] = createPublishedCourseWithLessons([true]);

    post(route('courses.enroll', $course->slug))
        ->assertRedirect(route('login'));
});

it('does not allow enrollment on draft courses', function (): void {
    $user = User::factory()->create(['email_verified_at' => now()]);
    [$course] = createDraftCourseWithLessons([true]);

    actingAs($user)
        ->post(route('courses.enroll', $course->slug))
        ->assertNotFound();
});

it('enrollment is idempotent through HTTP endpoint', function (): void {
    $user = User::factory()->create(['email_verified_at' => now()]);
    [$course] = createPublishedCourseWithLessons([true]);

    actingAs($user)->post(route('courses.enroll', $course->slug))->assertRedirect();
    actingAs($user)->post(route('courses.enroll', $course->slug))->assertRedirect();

    expect(Enrollment::query()
        ->where('user_id', $user->id)
        ->where('course_id', $course->id)
        ->count())->toBe(1);
});

it('allows guests to access free preview lessons and blocks non-preview lessons', function (): void {
    [$course, $lessons] = createPublishedCourseWithLessons([true, false]);

    get(route('courses.lessons.show', ['course' => $course->slug, 'lesson' => $lessons[0]->slug]))
        ->assertOk()
        ->assertSee($lessons[0]->title);

    get(route('courses.lessons.show', ['course' => $course->slug, 'lesson' => $lessons[1]->slug]))
        ->assertForbidden();
});

it('records lesson completion into lesson_progress', function (): void {
    $user = User::factory()->create(['email_verified_at' => now()]);
    [$course, $lessons] = createPublishedCourseWithLessons([true]);

    actingAs($user)
        ->postJson(route('courses.lessons.progress', ['course' => $course->slug, 'lesson' => $lessons[0]->slug]), [
            'watch_seconds' => 120,
            'completed' => true,
        ])
        ->assertOk()
        ->assertJson(['status' => 'ok']);

    $progress = LessonProgress::query()
        ->where('user_id', $user->id)
        ->where('course_id', $course->id)
        ->where('lesson_id', $lessons[0]->id)
        ->first();

    expect($progress)->not->toBeNull();
    expect($progress?->watch_seconds)->toBe(120);
    expect($progress?->completed_at)->not->toBeNull();
});

it('creates course completion and sends completion email once after all lessons are completed', function (): void {
    Mail::fake();

    $user = User::factory()->create(['email_verified_at' => now()]);
    [$course, $lessons] = createPublishedCourseWithLessons([true, true]);

    actingAs($user)->postJson(
        route('courses.lessons.progress', ['course' => $course->slug, 'lesson' => $lessons[0]->slug]),
        ['completed' => true]
    )->assertOk();

    $this->assertDatabaseMissing('course_completions', [
        'user_id' => $user->id,
        'course_id' => $course->id,
    ]);

    actingAs($user)->postJson(
        route('courses.lessons.progress', ['course' => $course->slug, 'lesson' => $lessons[1]->slug]),
        ['completed' => true]
    )->assertOk();

    $this->assertDatabaseHas('course_completions', [
        'user_id' => $user->id,
        'course_id' => $course->id,
    ]);

    Mail::assertSent(CourseCompletedMail::class, 1);

    actingAs($user)->postJson(
        route('courses.lessons.progress', ['course' => $course->slug, 'lesson' => $lessons[1]->slug]),
        ['completed' => true]
    )->assertOk();

    Mail::assertSent(CourseCompletedMail::class, 1);
});

it('does not let a user modify another users progress record', function (): void {
    $owner = User::factory()->create(['email_verified_at' => now()]);
    $otherUser = User::factory()->create(['email_verified_at' => now()]);
    [$course, $lessons] = createPublishedCourseWithLessons([true]);
    $lesson = $lessons[0];

    LessonProgress::create([
        'user_id' => $owner->id,
        'course_id' => $course->id,
        'lesson_id' => $lesson->id,
        'watch_seconds' => 10,
    ]);

    actingAs($otherUser)->postJson(
        route('courses.lessons.progress', ['course' => $course->slug, 'lesson' => $lesson->slug]),
        ['watch_seconds' => 200]
    )->assertOk();

    $ownerProgress = LessonProgress::query()
        ->where('user_id', $owner->id)
        ->where('lesson_id', $lesson->id)
        ->first();

    $otherProgress = LessonProgress::query()
        ->where('user_id', $otherUser->id)
        ->where('lesson_id', $lesson->id)
        ->first();

    expect($ownerProgress?->watch_seconds)->toBe(10);
    expect($otherProgress?->watch_seconds)->toBe(200);
});

it('does not let a user modify another users enrollment record', function (): void {
    $owner = User::factory()->create(['email_verified_at' => now()]);
    $otherUser = User::factory()->create(['email_verified_at' => now()]);
    [$course] = createPublishedCourseWithLessons([true]);

    Enrollment::create([
        'user_id' => $owner->id,
        'course_id' => $course->id,
        'enrolled_at' => now(),
    ]);

    actingAs($otherUser)->post(route('courses.enroll', $course->slug))->assertRedirect();

    $this->assertDatabaseHas('enrollments', [
        'user_id' => $owner->id,
        'course_id' => $course->id,
    ]);
    $this->assertDatabaseHas('enrollments', [
        'user_id' => $otherUser->id,
        'course_id' => $course->id,
    ]);
});

/**
 * @param array<int, bool> $freePreviewFlags
 * @return array{0: Course, 1: array<int, Lesson>}
 */
function createPublishedCourseWithLessons(array $freePreviewFlags): array
{
    return createCourseWithLessonsForFlow($freePreviewFlags, true);
}

/**
 * @param array<int, bool> $freePreviewFlags
 * @return array{0: Course, 1: array<int, Lesson>}
 */
function createDraftCourseWithLessons(array $freePreviewFlags): array
{
    return createCourseWithLessonsForFlow($freePreviewFlags, false);
}

/**
 * @param array<int, bool> $freePreviewFlags
 * @return array{0: Course, 1: array<int, Lesson>}
 */
function createCourseWithLessonsForFlow(array $freePreviewFlags, bool $published): array
{
    $level = Level::create([
        'name' => 'Level ' . str()->random(5),
        'slug' => 'level-' . str()->lower(str()->random(8)),
    ]);

    $course = Course::create([
        'level_id' => $level->id,
        'title' => 'Course ' . str()->random(5),
        'slug' => 'course-' . str()->lower(str()->random(8)),
        'description' => 'Test course',
        'is_published' => $published,
    ]);

    $lessons = [];
    foreach (array_values($freePreviewFlags) as $index => $isFreePreview) {
        $order = $index + 1;
        $lessons[] = Lesson::create([
            'course_id' => $course->id,
            'title' => "Lesson {$order}",
            'order' => $order,
            'video_url' => "https://example.com/video-{$order}",
            'is_free_preview' => $isFreePreview,
        ]);
    }

    return [$course, $lessons];
}
