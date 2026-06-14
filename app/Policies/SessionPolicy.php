<?php

namespace App\Policies;

use App\Models\Engagement;
use App\Models\Session;
use App\Models\User;

class SessionPolicy
{
    public function viewAny(User $user, Engagement $engagement): bool
    {
        return match ($user->role) {
            'branch_manager' => true,

            'track_admin' => $engagement
                ->cohort
                ->trackAdmins()
                ->where('users.id', $user->id)
                ->exists(),

            'instructor' => $engagement->instructor_id === $user->id,

            'student' => $user->labGroups()->where('cohort_id', $engagement->cohort_id)->exists(),

            default => false,
        };
    }

    public function view(User $user, Session $session): bool
    {
        return match ($user->role) {
            'branch_manager' => true,

            'track_admin' => $session
                ->engagement
                ->cohort
                ->trackAdmins()
                ->where('users.id', $user->id)
                ->exists(),

            'instructor' => $session->engagement->instructor_id === $user->id,

            default => false,
        };
    }

    public function create(User $user, Engagement $engagement): bool
    {
        return match ($user->role) {
            'track_admin' => $engagement
                ->cohort
                ->trackAdmins()
                ->where('users.id', $user->id)
                ->exists(),

            default => false,
        };
    }

    public function delete(User $user, Session $session): bool
    {
        return $this->create($user, $session->engagement);
    }

    public function deliver(User $user, Session $session): bool
    {
        return match ($user->role) {
            'track_admin' => $session
                ->engagement
                ->cohort
                ->trackAdmins()
                ->where('users.id', $user->id)
                ->exists(),

            'instructor' => $session->engagement->instructor_id === $user->id,

            default => false,
        };
    }
}
