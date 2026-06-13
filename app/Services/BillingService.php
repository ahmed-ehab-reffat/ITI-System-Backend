<?php

namespace App\Services;

use App\Models\Session;
use App\Models\User;
use App\Models\BillingRecord;

class BillingService
{
    /**
     * Called automatically by SessionController::deliver()
     * Creates one BillingRecord per delivered session.
     */
    public function recordForSession(Session $session): BillingRecord
    {
        $session->loadMissing('engagement.instructor');
        $engagement = $session->engagement;
        $instructor = $engagement->instructor;

        return BillingRecord::create([
            'user_id'     => $instructor->id,
            'session_id'  => $session->id,
            'hours'       => $engagement->hours_per_session,
            'person_type' => $instructor->compensation_type,
        ]);
    }

    /**
     * Calculate total compensation for one person across all their sessions.
     */
    public function rollupForUser(User $user): array
    {
        $records = BillingRecord::where('user_id', $user->id)
            ->with('session.engagement')
            ->get();

        $totalHours = $records->sum('hours');

        if ($user->compensation_type === 'internal') {
            $total = $user->fixed_salary + ($totalHours * ($user->hourly_rate ?? 0));
        } else {
            $total = $totalHours * ($user->hourly_rate ?? 0);
        }

        return [
            'instructor_id'   => $user->id,
            'instructor_name' => $user->name,
            'type'            => $user->compensation_type,
            'total_hours'     => $totalHours,
            'total_due'       => $total,
        ];
    }
}