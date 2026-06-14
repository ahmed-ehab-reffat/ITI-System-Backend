<?php

namespace App\Policies;

use App\Models\Engagement;
use App\Models\Session;
use App\Models\User;

class SessionPolicy
{
    
     // Instructors see sessions for their own engagements only.
     // Track admins and branch managers see all.
     
    public function viewAny(User $user, Engagement $engagement): bool
    {
        
        return match ($user->role) {
            'branch_manager', 'track_admin' => true,
            'instructor' => $engagement->instructor_id === $user->id,
            'student' => $user->labGroups()->where('cohort_id', $engagement->cohort_id)->exists(),
            default => false,
        };
        
    }

    public function view(User $user, Session $session): bool
    {
        
        return match ($user->role) {
            'branch_manager', 'track_admin' => true,
            'instructor' => $session->engagement->instructor_id === $user->id,
            default => false,
        };
        
    }

    
     // Only track admins may create sessions inside an engagement.
     
    public function create(User $user, Engagement $engagement): bool
    {
        
        if ($user->role !== 'track_admin') {
            return false;
        }

        // Track admin must own the cohort this engagement belongs to
        return $engagement->cohort->trackAdmins()->where('user_id', $user->id)->exists();
        
    }

    public function delete(User $user, Session $session): bool
    {
        return $this->create($user, $session->engagement);
    }

    
     // Instructors deliver their own sessions; track admins can deliver any.
     
    public function deliver(User $user, Session $session): bool
    {
        
        return match ($user->role) {
            'track_admin' => $session->engagement->cohort->trackAdmins()
                ->where('user_id', $user->id)->exists(),
            'instructor'  => $session->engagement->instructor_id === $user->id,
            default       => false,
        };
        
    }
}