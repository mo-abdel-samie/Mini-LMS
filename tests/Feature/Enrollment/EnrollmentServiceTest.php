<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Modules\Course\Models\Course;
use Modules\Enrollment\Models\Enrollment;
use Modules\Enrollment\Services\EnrollmentServiceInterface;
use Modules\Level\Models\Level;

uses(RefreshDatabase::class);

it('enrolls a user in a course and is idempotent', function (): void {
    Carbon::setTestNow('2026-02-19 12:00:00');

    $service = app(EnrollmentServiceInterface::class);
    $user = User::factory()->create();
    $course = createCourse();

    $first = $service->enroll($user->id, $course->id);
    $second = $service->enroll($user->id, $course->id);

    expect($first->id)->toBe($second->id);
    expect(Enrollment::count())->toBe(1);
    expect($first->enrolled_at?->toDateTimeString())->toBe('2026-02-19 12:00:00');

    Carbon::setTestNow();
});

it('checks enrollment status correctly', function (): void {
    $service = app(EnrollmentServiceInterface::class);
    $user = User::factory()->create();
    $course = createCourse();

    expect($service->isEnrolled($user->id, $course->id))->toBeFalse();

    $service->enroll($user->id, $course->id);

    expect($service->isEnrolled($user->id, $course->id))->toBeTrue();
});

it('returns user enrollments ordered by latest enrolled_at first', function (): void {
    $service = app(EnrollmentServiceInterface::class);
    $user = User::factory()->create();

    $level = Level::create([
        'name' => 'Intermediate',
        'slug' => 'intermediate',
    ]);

    $olderCourse = Course::create([
        'level_id' => $level->id,
        'title' => 'Older Course',
        'slug' => 'older-course',
        'is_published' => false,
    ]);

    $newerCourse = Course::create([
        'level_id' => $level->id,
        'title' => 'Newer Course',
        'slug' => 'newer-course',
        'is_published' => false,
    ]);

    Enrollment::create([
        'user_id' => $user->id,
        'course_id' => $olderCourse->id,
        'enrolled_at' => '2026-02-18 10:00:00',
    ]);

    Enrollment::create([
        'user_id' => $user->id,
        'course_id' => $newerCourse->id,
        'enrolled_at' => '2026-02-19 10:00:00',
    ]);

    $enrollments = $service->getUserEnrollments($user->id);

    expect($enrollments)->toHaveCount(2);
    expect($enrollments->first()->course->title)->toBe('Newer Course');
    expect($enrollments->last()->course->title)->toBe('Older Course');
});

function createCourse(): Course
{
    $level = Level::create([
        'name' => 'Beginner',
        'slug' => 'beginner',
    ]);

    return Course::create([
        'level_id' => $level->id,
        'title' => 'Course ' . str()->random(8),
        'slug' => 'course-' . str()->lower(str()->random(8)),
        'is_published' => false,
    ]);
}
