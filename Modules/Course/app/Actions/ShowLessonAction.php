<?php

namespace Modules\Course\Actions;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Modules\Course\Models\Course;
use Modules\Course\Models\Lesson;
use Modules\Course\Services\LessonViewServiceInterface;

class ShowLessonAction
{
    public function __construct(
        private readonly LessonViewServiceInterface $lessonViewService
    ) {}

    public function __invoke(Course $course, Lesson $lesson): View
    {
        abort_unless($course->is_published, 404);

        $payload = $this->lessonViewService->getShowLessonPayload(
            $course,
            $lesson,
            Auth::check() ? Auth::id() : null
        );

        return view('courses.lesson', $payload);
    }
}
