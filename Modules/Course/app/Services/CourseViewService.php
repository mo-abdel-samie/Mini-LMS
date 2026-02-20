<?php

namespace Modules\Course\Services;

use Modules\Course\Models\Course;
use Modules\Course\Repositories\CourseViewRepositoryInterface;
use Modules\Enrollment\Services\EnrollmentServiceInterface;

class CourseViewService implements CourseViewServiceInterface
{
    public function __construct(
        private readonly CourseViewRepositoryInterface $courseViewRepository,
        private readonly EnrollmentServiceInterface $enrollmentService,
    ) {}

    public function getShowCoursePayload(Course $course, ?int $userId): array
    {
        $isEnrolled = $userId !== null
            ? $this->enrollmentService->isEnrolled($userId, (int) $course->id)
            : false;

        $course = $this->courseViewRepository->loadCourseForShow($course);

        $lessons = $course->lessons;

        $courseProgress = null;
        $completedLessonIds = [];

        if ($userId !== null && $this->courseViewRepository->hasLessonProgressTable()) {
            $completedLessons = $this->courseViewRepository->getCompletedLessonsCount($userId, (int) $course->id);

            $totalLessons = (int) $course->lessons_count;
            $percent = $totalLessons > 0 ? (int) min(100, round(($completedLessons / $totalLessons) * 100)) : 0;

            $courseProgress = [
                'completed' => $completedLessons,
                'total' => $totalLessons,
                'percent' => $percent,
            ];

            $completedLessonIds = $this->courseViewRepository->getCompletedLessonIds($userId, (int) $course->id);
        }

        return [
            'course' => $course,
            'isEnrolled' => $isEnrolled,
            'lessons' => $lessons,
            'courseProgress' => $courseProgress,
            'completedLessonIds' => $completedLessonIds,
        ];
    }
}
