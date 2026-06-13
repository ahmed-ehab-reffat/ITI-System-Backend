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
        // ── Tracks ──────────────────────────────────────────────────────────────
        $webTrack = Track::create([
            'name'        => 'Web Development',
            'code'        => 'WD',
            'description' => 'Full-stack web development using Laravel & Vue.js.',
        ]);

        $mobileTrack = Track::create([
            'name'        => 'Mobile Development',
            'code'        => 'MD',
            'description' => 'Cross-platform mobile development using Flutter & Dart.',
        ]);

        // ── Cohorts (one active per track — LC-1) ───────────────────────────────
        $webCohort = Cohort::create([
            'track_id'  => $webTrack->id,
            'name'      => 'Web Development — Intake 45',
            'status'    => 'active',
            'starts_at' => now()->subWeeks(10),
            'ends_at'   => now()->addMonths(5),
        ]);

        $mobileCohort = Cohort::create([
            'track_id'  => $mobileTrack->id,
            'name'      => 'Mobile Development — Intake 12',
            'status'    => 'active',
            'starts_at' => now()->subWeeks(8),
            'ends_at'   => now()->addMonths(6),
        ]);

        // ── Attach Track Admins (LC-2) ───────────────────────────────────────────
        $webAdmin    = User::where('email', 'admin.web@iti.test')->first();
        $mobileAdmin = User::where('email', 'admin.mobile@iti.test')->first();

        $webCohort->trackAdmins()->attach($webAdmin->id);
        $mobileCohort->trackAdmins()->attach($mobileAdmin->id);

        // ── Attendance Ledgers (ATT-4: balance starts at 250) ────────────────────
        // Students 1–30 belong to Web Dev, students 31–46 to Mobile Dev
        $webStudents    = User::where('role', 'student')->orderBy('created_at')->take(30)->get();
        $mobileStudents = User::where('role', 'student')->orderBy('created_at')->skip(30)->take(16)->get();

        foreach ($webStudents as $student) {
            AttendanceLedger::create([
                'student_id' => $student->id,
                'cohort_id'  => $webCohort->id,
                'balance'    => 250,
            ]);
        }

        foreach ($mobileStudents as $student) {
            AttendanceLedger::create([
                'student_id' => $student->id,
                'cohort_id'  => $mobileCohort->id,
                'balance'    => 250,
            ]);
        }
    }
}