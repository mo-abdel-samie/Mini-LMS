<?php

namespace Modules\Enrollment\Services;

use Illuminate\Support\Collection;
use Modules\Enrollment\Models\Enrollment;

interface EnrollmentServiceInterface
{
    public function enroll(int $userId, int $courseId): Enrollment;

    public function unenroll(int $userId, int $courseId): bool;

    public function isEnrolled(int $userId, int $courseId): bool;

    public function getUserEnrollments(int $userId): Collection;
}

