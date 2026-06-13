<?php

namespace App\Policies;

use App\Models\Session;
use App\Models\Submission;
use App\Models\User;

class SubmissionPolicy
{
    public function viewAny(User $user, Session $session): bool
    {
        return match ($user->role) {
            'branch_manager', 'track_admin' => true,
            'instructor' => $session->engagement->instructor_id === $user->id,
            default      => false,
        };
    }

    public function view(User $user, Submission $submission): bool
    {
        if ($user->isStudent()) {
            return $submission->student_id === $user->id;
        }

        $session = $submission->session;

        return match ($user->role) {
            'branch_manager', 'track_admin' => true,
            'instructor' => $session->engagement->instructor_id === $user->id,
            default      => false,
        };
    }

    public function create(User $user, Session $session): bool
    {
        return $user->isStudent();
    }

    // GRD-4: instructors grade only their assigned lab group
    public function grade(User $user, Submission $submission): bool
    {
        if (! $user->isInstructor()) {
            return false;
        }

        $engagement = $submission->session->engagement;

        if ($engagement->instructor_id !== $user->id) {
            return false;
        }

        $labGroupId = $engagement->lab_group_id;

        if (! $labGroupId) {
            return false;
        }

        return $submission->student
            ->labGroups()
            ->where('lab_groups.id', $labGroupId)
            ->exists();
    }

    public function viewStudentSubmissions(User $user, User $student): bool
    {
        return match ($user->role) {
            'branch_manager', 'track_admin', 'instructor' => true,
            'student' => $user->id === $student->id,
            default   => false,
        };
    }
}
