<?php

namespace Database\Seeders;

use App\Models\AttendanceRecord;
use App\Models\ExcuseRequest;
use App\Models\Session;
use App\Models\User;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $sessions = Session::with('engagement.cohort')->get();
        $students = User::where('role', 'student')->get();

        foreach ($sessions as $sessionKey => $session) {
            $engagement = $session->engagement;
            $cohort = $engagement->cohort;

            // Determine students eligible for this session
            if ($engagement->lab_group_id) {
                // Only students in the engagement's lab group
                $eligibleStudents = User::whereHas('labGroups', function ($query) use ($engagement) {
                    $query->where('lab_groups.id', $engagement->lab_group_id);
                })->get();
            } else {
                // All students in the cohort (via attendance ledger or just all students for local simplicity)
                $eligibleStudents = $students;
            }

            foreach ($eligibleStudents as $index => $student) {
                // Make student 1 present, student 2 absent, etc., or randomly
                $status = 'present';
                $arrivedAt = null;
                $leftAt = null;

                if ($index === 0 && $sessionKey % 2 === 0) {
                    $status = 'absent';
                } elseif ($index === 1 && $sessionKey % 2 === 1) {
                    $status = 'excused';
                } else {
                    $arrivedAt = now()->subWeeks(3)->setTime(9, rand(0, 15), 0);
                    $leftAt = now()->subWeeks(3)->setTime(12, rand(0, 5), 0);
                }

                $record = AttendanceRecord::create([
                    'session_id' => $session->id,
                    'student_id' => $student->id,
                    'arrived_at' => $arrivedAt,
                    'left_at'    => $leftAt,
                    'status'     => $status,
                ]);

                if ($status === 'excused') {
                    ExcuseRequest::create([
                        'attendance_record_id' => $record->id,
                        'student_id'           => $student->id,
                        'reason'               => 'Medical appointment',
                        'attachment_path'      => 'excuses/medical.pdf',
                        'status'               => 'requested',
                    ]);
                }
            }
        }
    }
}
