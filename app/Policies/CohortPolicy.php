<?php

namespace App\Policies;

use App\Models\Cohort;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CohortPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isBranchManager() || $user->isTrackAdmin();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Cohort $cohort): bool
    {
        if ($user->isBranchManager()) {
            return true;
        }

        if ($user->isTrackAdmin()) {
            return $cohort->trackAdmins()->where('users.id', $user->id)->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isBranchManager();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Cohort $cohort): bool
    {
        return $user->isBranchManager();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Cohort $cohort): bool
    {
        return $user->isBranchManager();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Cohort $cohort): bool
    {
        return $user->isBranchManager();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Cohort $cohort): bool
    {
        return $user->isBranchManager();
    }
}
