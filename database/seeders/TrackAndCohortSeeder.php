<?php

namespace Database\Seeders;

use App\Models\AttendanceLedger;
use App\Models\Cohort;
use App\Models\Track;
use App\Models\User;
use Illuminate\Database\Seeder;

class TrackAndCohortSeeder extends Seeder
{
    public function run(): void
    {
        $track = Track::create([
            'name' => 'Web Development',
            'description' => 'Full-stack web track',
        ]);

        $cohort = Cohort::create([
            'track_id' => $track->id,
            'name' => 'Intake 45',
            'status' => 'active',
            'starts_at' => now()->subMonths(1),
            'ends_at' => now()->addMonths(9),
        ]);

        // Attach track admin
        $admin = User::where('role', 'track_admin')->first();
        $cohort->trackAdmins()->attach($admin->id);

        // Create attendance ledger for each student
        $students = User::where('role', 'student')->get();
        foreach ($students as $student) {
            AttendanceLedger::create([
                'student_id' => $student->id,
                'cohort_id' => $cohort->id,
                'balance' => 250,
            ]);
        }
    }
}
