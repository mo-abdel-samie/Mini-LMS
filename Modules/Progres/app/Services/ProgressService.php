<?php

namespace Modules\Progres\Services;

use App\Mail\CourseCompletedMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Modules\Course\Models\Course;
use Illuminate\Support\Collection;
use Modules\Course\Models\Lesson;
use Modules\Progres\Models\CourseCompletion;
use Modules\Progres\Models\LessonProgress;
use Modules\Progres\Repositories\CourseCompletionRepositoryInterface;
use Modules\Progres\Repositories\LessonProgressRepositoryInterface;
use Throwable;

class ProgressService implements ProgressServiceInterface
{
    public function __construct(
        private readonly LessonProgressRepositoryInterface $lessonProgressRepository,
        private readonly CourseCompletionRepositoryInterface $courseCompletionRepository,
    ) {}

    public function startLesson(int $userId, int $courseId, int $lessonId): LessonProgress
    {
        return $this->lessonProgressRepository->markStarted($userId, $courseId, $lessonId);
    }

    public function updateWatchSeconds(int $userId, int $courseId, int $lessonId, int $watchSeconds): LessonProgress
    {
        return $this->lessonProgressRepository->updateWatchSeconds($userId, $courseId, $lessonId, $watchSeconds);
    }

    public function completeLesson(int $userId, int $courseId, int $lessonId): LessonProgress
    {
        $progress = $this->lessonProgressRepository->markCompleted($userId, $courseId, $lessonId);

        $this->syncCourseCompletion($userId, $courseId);

        return $progress;
    }

    public function syncCourseCompletion(int $userId, int $courseId): ?CourseCompletion
    {
        $totalLessons = Lesson::where('course_id', $courseId)->count();
        $completedLessonIds = $this->lessonProgressRepository->getCompletedLessonIdsByCourse($userId, $courseId);
        $completedCount = $completedLessonIds->unique()->count();
        $wasCompleted = $this->courseCompletionRepository->isCompleted($userId, $courseId);

        if ($totalLessons > 0 && $completedCount >= $totalLessons) {
            $completion = $this->courseCompletionRepository->markCompleted($userId, $courseId);

            if (! $wasCompleted) {
                $this->sendCourseCompletionEmail($userId, $courseId);
            }

            return $completion;
        }

        $this->courseCompletionRepository->removeCompletion($userId, $courseId);

        return null;
    }

    public function isCourseCompleted(int $userId, int $courseId): bool
    {
        return $this->courseCompletionRepository->isCompleted($userId, $courseId);
    }

    public function getCompletedLessonIds(int $userId, int $courseId): Collection
    {
        return $this->lessonProgressRepository->getCompletedLessonIdsByCourse($userId, $courseId)->unique()->values();
    }

    private function sendCourseCompletionEmail(int $userId, int $courseId): void
    {
        $user = User::find($userId);
        $course = Course::find($courseId);

        if (! $user || ! $course || ! $user->email) {
            return;
        }

        try {
            Mail::to($user->email)->send(new CourseCompletedMail($user, $course));
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
