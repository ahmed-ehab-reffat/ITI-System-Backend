<?php

namespace App\Services;

use Carbon\Carbon;

class LatePenaltyService
{
    /**
     * Calculate whole days late between the session due date and submission time.
     */
    public function daysLate(Carbon $dueDate, Carbon $submittedAt): int
    {
        if ($submittedAt->lte($dueDate)) {
            return 0;
        }

        return (int) $dueDate->copy()->startOfDay()->diffInDays($submittedAt->copy()->startOfDay());
    }

    /**
     * ENG-2: penalty = daysLate × 0.25 × 10
     */
    public function calculate(int $daysLate): float
    {
        if ($daysLate <= 0) {
            return 0.0;
        }

        $multiplier = config('grading.late_penalty_multiplier');
        $base       = config('grading.late_penalty_base');

        return round($daysLate * $multiplier * $base, 2);
    }

    /**
     * final_score = max(0, raw_score - late_penalty)
     */
    public function finalScore(?float $rawScore, float $latePenalty): ?float
    {
        if ($rawScore === null) {
            return null;
        }

        return max(0, round($rawScore - $latePenalty, 2));
    }
}
