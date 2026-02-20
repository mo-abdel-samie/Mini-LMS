<?php

namespace Modules\Enrollment\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Modules\Enrollment\Models\Enrollment;
use Modules\Enrollment\Repositories\EnrollmentRepositoryInterface;

class EnrollmentService implements EnrollmentServiceInterface
{
    public function __construct(
        private readonly EnrollmentRepositoryInterface $enrollmentRepository
    ) {}

    public function enroll(int $userId, int $courseId): Enrollment
    {
        return $this->enrollmentRepository->firstOrCreate(
            [
                'user_id' => $userId,
                'course_id' => $courseId,
            ],
            [
                'enrolled_at' => Carbon::now(),
            ]
        );
    }

    public function unenroll(int $userId, int $courseId): bool
    {
        $enrollment = $this->enrollmentRepository->findByUserAndCourse($userId, $courseId);

        if (! $enrollment) {
            return false;
        }

        return $this->enrollmentRepository->delete($enrollment);
    }

    public function isEnrolled(int $userId, int $courseId): bool
    {
        return $this->enrollmentRepository->findByUserAndCourse($userId, $courseId) !== null;
    }

    public function getUserEnrollments(int $userId): Collection
    {
        return $this->enrollmentRepository->getByUser($userId);
    }
}

