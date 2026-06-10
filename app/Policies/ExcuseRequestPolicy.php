<?php

namespace App\Policies;

use App\Models\ExcuseRequest;
use App\Models\User;

class ExcuseRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStudent() || $user->isTrackAdmin() || $user->isBranchManager();
    }

    public function view(User $user, ExcuseRequest $excuseRequest): bool
    {
        if ($user->isStudent()) {
            return $excuseRequest->student_id === $user->id;
        }

        if ($user->isTrackAdmin()) {
            return $this->managesExcuseCohort($user, $excuseRequest);
        }

        return $user->isBranchManager();
    }

    public function create(User $user): bool
    {
        return $user->isStudent();
    }

    public function approve(User $user, ExcuseRequest $excuseRequest): bool
    {
        if (! $user->isTrackAdmin()) {
            return false;
        }

        return $this->managesExcuseCohort($user, $excuseRequest);
    }

    public function reject(User $user, ExcuseRequest $excuseRequest): bool
    {
        if (! $user->isTrackAdmin()) {
            return false;
        }

        return $this->managesExcuseCohort($user, $excuseRequest);
    }

    private function managesExcuseCohort(User $user, ExcuseRequest $excuseRequest): bool
    {
        $cohortId = $excuseRequest->attendanceRecord
            ?->session
            ?->engagement
            ?->cohort_id;

        if (! $cohortId) {
            return false;
        }

        return $user->managedCohorts()->where('cohorts.id', $cohortId)->exists();
    }
}
