<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\CourseGrade;
use App\Models\User;

class CourseGradePolicy
{
    public function viewAny(User $user, Course $course): bool
    {
        return $user->isTrackAdmin() || $user->isBranchManager();
    }

    public function view(User $user, CourseGrade $courseGrade): bool
    {
        if ($user->isStudent()) {
            return $courseGrade->student_id === $user->id;
        }

        return $user->isTrackAdmin() || $user->isBranchManager();
    }

    public function create(User $user): bool
    {
        return $user->isTrackAdmin();
    }

    public function viewSummary(User $user, User $student): bool
    {
        return match ($user->role) {
            'branch_manager', 'track_admin', 'instructor' => true,
            'student' => $user->id === $student->id,
            default   => false,
        };
    }

    public function viewOverrides(User $user, CourseGrade $courseGrade): bool
    {
        return $user->isTrackAdmin() || $user->isBranchManager();
    }

    public function override(User $user, CourseGrade $courseGrade): bool
    {
        return $user->isTrackAdmin();
    }
}
