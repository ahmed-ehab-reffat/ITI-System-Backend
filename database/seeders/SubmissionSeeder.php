<?php

namespace Database\Seeders;

use App\Models\Session;
use App\Models\Submission;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class SubmissionSeeder extends Seeder
{
    public function run(): void
    {
        $labSessions = Session::where('is_delivered', true)
            ->whereHas('engagement', fn ($q) => $q->where('type', 'lab'))
            ->with('engagement')
            ->get();

        foreach ($labSessions as $session) {
            // Direct pivot query — no User relationship needed
            $studentIds = DB::table('lab_group_student')
                ->where('lab_group_id', $session->engagement->lab_group_id)
                ->pluck('user_id');

            $students = User::whereIn('id', $studentIds)->get();

            $sessionDate = Carbon::parse($session->session_date);

            foreach ($students as $index => $student) {
                // ~20% of students did not submit
                if ($index % 5 === 4) continue;

                $daysLate    = match ($index % 4) { 0 => 0, 1 => 1, 2 => 2, 3 => 4 };
                $submittedAt = (clone $sessionDate)->addDays($daysLate)->setTime(21, rand(0, 59));
                $rawScore    = rand(6, 10);
                $penaltyRate = min($daysLate * 0.25, 1.0);
                $latePenalty = round($penaltyRate * $rawScore, 2);
                $finalScore  = round(max($rawScore - $latePenalty, 0), 2);

                Submission::create([
                    'session_id'   => $session->id,
                    'student_id'   => $student->id,
                    'url'          => "https://github.com/iti-student-{$index}/lab-{$session->id}",
                    'file_path'    => null,
                    'submitted_at' => $submittedAt,
                    'raw_score'    => $finalScore,
                    'late_penalty' => $latePenalty,
                ]);
            }
        }
    }
}