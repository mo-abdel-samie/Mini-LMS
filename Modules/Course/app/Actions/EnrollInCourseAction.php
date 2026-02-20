<?php

namespace Modules\Course\Actions;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Modules\Course\Models\Course;
use Modules\Enrollment\Services\EnrollmentServiceInterface;

class EnrollInCourseAction
{
    public function __invoke(Course $course, EnrollmentServiceInterface $enrollmentService): RedirectResponse
    {
        abort_unless($course->is_published, 404);

        $enrollmentService->enroll( Auth::id(), $course->id);

        return back()->with('status', 'You are enrolled in "'.$course->title.'".');
    }
}
