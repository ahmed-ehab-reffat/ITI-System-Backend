<?php

namespace App\Policies;

use App\Models\StudentTag;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class StudentTagPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['track_admin', 'branch_manager', 'instructor']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, StudentTag $studentTag): bool
    {
        return in_array($user->role, ['track_admin', 'branch_manager', 'instructor']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['track_admin', 'instructor']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, StudentTag $studentTag): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, StudentTag $studentTag): bool
    {
        if ($user->isTrackAdmin()) return true;
        return $studentTag->tagged_by === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, StudentTag $studentTag): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, StudentTag $studentTag): bool
    {
        return false;
    }
}
