<?php

namespace Modules\Course\Actions;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Modules\Course\Models\Course;
use Modules\Course\Services\CourseViewServiceInterface;

class ShowCourseAction
{
    public function __construct(
        private readonly CourseViewServiceInterface $courseViewService
    ) {}

    public function __invoke(Course $course): View
    {
        abort_unless($course->is_published, 404);

        $payload = $this->courseViewService->getShowCoursePayload(
            $course,
            Auth::check() ? Auth::id() : null
        );

        return view('courses.show', $payload);
    }
}
