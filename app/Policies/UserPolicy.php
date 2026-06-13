<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     * Allowed for: branch_manager, track_admin.
     */
    public function viewAny(User $user): bool
    {
        return $user->isBranchManager() || $user->isTrackAdmin();
    }

    /**
     * Determine whether the user can view the model.
     * Allowed for: branch_manager, track_admin.
     */
    public function view(User $user, User $model): bool
    {
        return $user->isBranchManager() || $user->isTrackAdmin();
    }

    /**
     * Determine whether the user can create models.
     * - branch_manager: can create ANY role.
     * - track_admin: can ONLY create 'instructor' or 'student'.
     */
    public function create(User $user): bool
    {
        if ($user->isBranchManager()) {
            return true;
        }

        if ($user->isTrackAdmin()) {
            $requestedRole = request()->input('role');
            return in_array($requestedRole, ['instructor', 'student'], true);
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     * - branch_manager: can update anyone.
     * - track_admin: can only update instructors and students.
     */
    public function update(User $user, User $model): bool
    {
        if ($user->isBranchManager()) {
            return true;
        }

        if ($user->isTrackAdmin()) {
            return $model->isInstructor() || $model->isStudent();
        }

        return false;
    }

    /**
     * Determine whether the user can delete (deactivate) the model.
     * Only branch_manager can deactivate accounts.
     */
    public function delete(User $user, User $model): bool
    {
        return $user->isBranchManager();
    }
}
