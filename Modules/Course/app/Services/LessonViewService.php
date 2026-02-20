<?php

namespace Modules\Course\Services;

use Modules\Course\Models\Course;
use Modules\Course\Models\Lesson;
use Modules\Course\Repositories\LessonViewRepositoryInterface;
use Modules\Enrollment\Services\EnrollmentServiceInterface;
use Modules\Progres\Services\ProgressServiceInterface;

class LessonViewService implements LessonViewServiceInterface
{
    public function __construct(
        private readonly LessonViewRepositoryInterface $lessonViewRepository,
        private readonly EnrollmentServiceInterface $enrollmentService,
        private readonly ProgressServiceInterface $progressService,
    ) {}

    public function getShowLessonPayload(Course $course, Lesson $lesson, ?int $userId): array
    {
        abort_unless((int) $lesson->course_id === (int) $course->id, 404);

        $isEnrolled = $userId !== null
            ? $this->enrollmentService->isEnrolled($userId, (int) $course->id)
            : false;

        $canView = $isEnrolled || (bool) $lesson->is_free_preview;
        abort_unless($canView, 403);

        if ($userId !== null && $this->lessonViewRepository->hasLessonProgressTable()) {
            $this->progressService->startLesson($userId, (int) $course->id, (int) $lesson->id);
        }

        $course = $this->lessonViewRepository->loadCourseLevel($course);
        $accessibleLessons = $this->lessonViewRepository->getAccessibleLessons((int) $course->id, $isEnrolled);

        $currentIndex = $accessibleLessons->search(
            fn (Lesson $item): bool => (int) $item->id === (int) $lesson->id
        );

        $previousLesson = $currentIndex !== false ? $accessibleLessons->get($currentIndex - 1) : null;
        $nextLesson = $currentIndex !== false ? $accessibleLessons->get($currentIndex + 1) : null;

        return [
            'course' => $course,
            'lesson' => $lesson,
            'isEnrolled' => $isEnrolled,
            'previousLesson' => $previousLesson,
            'nextLesson' => $nextLesson,
        ];
    }
}

