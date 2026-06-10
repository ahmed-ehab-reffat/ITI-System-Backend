<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Submission;

class GradeComputationService
{
    
    public function normalizeExamScore(float $rawScore, float $rawMax, float $examWeight): float
    {
        if ($rawMax <= 0) {
            return 0.0;
        }

        return round(($rawScore / $rawMax) * $examWeight, 2);
    }

  
    public function computeLabScore(Course $course, string $studentId): float
    {
        $submissions = Submission::query()
            ->where('student_id', $studentId)
            ->whereNotNull('raw_score')
            ->whereHas('session.engagement', fn ($q) => $q
                ->where('cohort_id', $course->cohort_id)
                ->where('type', 'lab'))
            ->get();

        if ($submissions->isEmpty()) {
            return 0.0;
        }

        $averageFinal = $submissions->avg(function (Submission $submission) {
            $penalty = (float) $submission->late_penalty;

            return max(0, (float) $submission->raw_score - $penalty);
        });

        return round(($averageFinal / 10) * $course->lab_weight, 2);
    }

  
    public function courseTotal(Course $course, string $studentId, ?float $examComputedScore): float
    {
        $lab   = $this->computeLabScore($course, $studentId);
        $exam  = $examComputedScore ?? 0.0;

        return round($lab + $exam, 2);
    }
}
