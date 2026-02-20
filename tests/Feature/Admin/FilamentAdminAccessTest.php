<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Course\Models\Course;
use Modules\Course\Models\Lesson;
use Modules\Enrollment\Models\Enrollment;
use Modules\Level\Models\Level;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

it('allows only admins to access filament admin panel', function (): void {
    $regularUser = User::factory()->create(['email_verified_at' => now()]);

    $regularResponse = actingAs($regularUser)->get('/admin');
    expect($regularResponse->getStatusCode())->toBe(403);

    $admin = createAdminUser();
    actingAs($admin)->get('/admin')->assertOk();
});

it('renders levels and courses admin pages for admins', function (): void {
    $admin = createAdminUser();
    $level = Level::create([
        'name' => 'Test Level',
        'slug' => 'test-level',
    ]);
    $course = Course::create([
        'level_id' => $level->id,
        'title' => 'Test Course',
        'slug' => 'test-course',
        'is_published' => true,
    ]);
    Lesson::create([
        'course_id' => $course->id,
        'title' => 'Test Lesson',
        'order' => 1,
        'video_url' => 'https://example.com/test-lesson',
        'is_free_preview' => true,
    ]);

    actingAs($admin)->get('/admin/levels')->assertOk()->assertSee('Test Level');
    actingAs($admin)->get('/admin/courses')->assertOk()->assertSee('Test Course');
    actingAs($admin)->get("/admin/courses/{$course->id}/edit")->assertOk()->assertSee('Test Lesson');
});

it('keeps users resource read-only in admin panel', function (): void {
    $admin = createAdminUser();
    $user = User::factory()->create();

    actingAs($admin)->get('/admin/users')->assertOk()->assertSee($user->email);
    actingAs($admin)->get('/admin/users/create')->assertNotFound();
    actingAs($admin)->get("/admin/users/{$user->id}/edit")->assertNotFound();
});

it('renders enrollments page including progress view data for admins', function (): void {
    $admin = createAdminUser();
    $student = User::factory()->create(['email_verified_at' => now()]);
    $level = Level::create([
        'name' => 'Enrollment Level',
        'slug' => 'enrollment-level',
    ]);
    $course = Course::create([
        'level_id' => $level->id,
        'title' => 'Enrollment Course',
        'slug' => 'enrollment-course',
        'is_published' => true,
    ]);

    Lesson::create([
        'course_id' => $course->id,
        'title' => 'Lesson 1',
        'order' => 1,
        'video_url' => 'https://example.com/1',
        'is_free_preview' => true,
    ]);

    Enrollment::create([
        'user_id' => $student->id,
        'course_id' => $course->id,
        'enrolled_at' => now(),
    ]);

    actingAs($admin)->get('/admin/enrollments')
        ->assertOk()
        ->assertSee('Progress')
        ->assertSee('Enrollment Course');
});

function createAdminUser(): User
{
    $role = Role::findOrCreate('super_admin', 'web');
    $role->givePermissionTo(Permission::findOrCreate('view_any_user', 'web'));
    $role->givePermissionTo(Permission::findOrCreate('view_user', 'web'));

    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole($role);

    return $user;
}
