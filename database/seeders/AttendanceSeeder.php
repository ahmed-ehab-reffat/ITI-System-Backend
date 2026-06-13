<?php

namespace Database\Seeders;

use App\Models\AttendanceLedger;
use App\Models\AttendanceRecord;
use App\Models\ExcuseRequest;
use App\Models\Session;
use App\Models\User;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $sessions = Session::where('is_delivered', true)
            ->with('engagement.cohort')
            ->get();

        $admin = User::where('role', 'track_admin')->first();

        foreach ($sessions as $session) {
            $engagement = $session->engagement;
            $cohort     = $engagement->cohort;

            // Scope students: lab engagement → lab group only, else → full cohort
            if ($engagement->lab_group_id) {
                $students = User::whereHas('labGroups', function ($q) use ($engagement) {
                    $q->where('lab_groups.id', $engagement->lab_group_id);
                })->get();
            } else {
                // Query AttendanceLedger directly — no relationship needed on User model
                $studentIds = AttendanceLedger::where('cohort_id', $cohort->id)
                    ->pluck('student_id');
                $students = User::whereIn('id', $studentIds)->get();
            }

            foreach ($students as $index => $student) {
                $bucket = $index % 10;

                $status = match (true) {
                    $bucket <= 6  => 'present',
                    $bucket === 7 => 'absent',
                    $bucket === 8 => 'excused',
                    $bucket === 9 => 'absent',
                    default       => 'present',
                };

                $arrivedAt = null;
                $leftAt    = null;

                if ($status === 'present') {
                    $base      = $session->session_date instanceof \Carbon\Carbon
                        ? $session->session_date
                        : \Carbon\Carbon::parse($session->session_date);
                    $arrivedAt = (clone $base)->setTime(9, rand(0, 20));
                    $leftAt    = (clone $base)->setTime(12, rand(0, 15));
                }

                $record = AttendanceRecord::create([
                    'session_id' => $session->id,
                    'student_id' => $student->id,
                    'arrived_at' => $arrivedAt,
                    'left_at'    => $leftAt,
                    'status'     => $status,
                ]);

                // ── Ledger update (ATT-5) ────────────────────────────────────────
                $deduction = match ($status) {
                    'absent'  => -25,
                    'excused' => -5,
                    default   => 0,
                };

                if ($deduction !== 0) {
                    AttendanceLedger::where('student_id', $student->id)
                        ->where('cohort_id', $cohort->id)
                        ->decrement('balance', abs($deduction));
                }

                // ── Excuse requests ──────────────────────────────────────────────
                if ($bucket === 8) {
                    ExcuseRequest::create([
                        'attendance_record_id' => $record->id,
                        'student_id'           => $student->id,
                        'reason'               => 'Family emergency — hospital visit confirmed with documentation.',
                        'attachment_path'      => null,
                        'status'               => 'approved',
                        'reviewed_by'          => $admin->id,
                        'reviewer_note'        => 'Medical documentation verified. Excuse approved.',
                    ]);

                    // Refund 20 points (net -5 instead of -25)
                    AttendanceLedger::where('student_id', $student->id)
                        ->where('cohort_id', $cohort->id)
                        ->increment('balance', 20);
                }

                if ($bucket === 9) {
                    ExcuseRequest::create([
                        'attendance_record_id' => $record->id,
                        'student_id'           => $student->id,
                        'reason'               => 'I had a medical appointment that could not be rescheduled.',
                        'attachment_path'      => null,
                        'status'               => 'requested',
                        'reviewed_by'          => null,
                        'reviewer_note'        => null,
                    ]);
                }
            }
        }
    }
}