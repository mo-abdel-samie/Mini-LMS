<?php

namespace Modules\Course\Actions;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Course\Models\Course;
use Modules\Course\Models\Lesson;
use Modules\Enrollment\Models\Enrollment;
use Modules\Enrollment\Services\EnrollmentServiceInterface;
use Modules\Progres\Services\ProgressServiceInterface;

class TrackLessonProgressAction
{
    public function __invoke(
        Request $request,
        Course $course,
        Lesson $lesson,
        ProgressServiceInterface $progressService,
        EnrollmentServiceInterface $enrollmentService
    ) {
        abort_unless($course->is_published, 404);
        abort_unless($lesson->course_id === $course->id, 404);

        $userId = Auth::id();

        $isEnrolled = $enrollmentService->isEnrolled($userId, $course->id);

        $canTrack = $isEnrolled || $lesson->is_free_preview;
        abort_unless($canTrack, 403);

        $validated = $request->validate([
            'watch_seconds' => ['nullable', 'integer', 'min:0'],
            'completed' => ['nullable', 'boolean'],
        ]);

        $progressService->startLesson($userId, $course->id, $lesson->id);

        if (array_key_exists('watch_seconds', $validated)) {
            $progressService->updateWatchSeconds(
                $userId,
                $course->id,
                $lesson->id,
                (int) $validated['watch_seconds']
            );
        }

        if (! empty($validated['completed'])) {
            $progressService->completeLesson($userId, $course->id, $lesson->id);
        }

        return response()->json(['status' => 'ok']);
    }
}
