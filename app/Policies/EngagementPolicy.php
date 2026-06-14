<?php

namespace App\Policies;

use App\Models\Engagement;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class EngagementPolicy
{
    public function viewAny(User $user): bool
    {
        return true
    }

    public function view(User $user, Engagement $engagement): bool
    {
        return match ($user->role) {
            'branch_manager', 'track_admin' => true,
            'instructor' => $engagement->instructor_id === $user->id,
            // Strict check: Student can only view engagements matching their cohort
            'student' => $user->cohort_id === $engagement->cohort_id,
            default => false,
        };
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isTrackAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Engagement $engagement): bool
    {
        return $user->isTrackAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Engagement $engagement): bool
    {
        return $user->isTrackAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Engagement $engagement): bool
    {
        return $user->isTrackAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Engagement $engagement): bool
    {
        return $user->isTrackAdmin();
    }
}
