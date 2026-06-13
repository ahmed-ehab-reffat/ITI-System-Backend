<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seeding order — each step depends on the one before it:
     *
     *  1. Users              — every table references users
     *  2. Tracks & Cohorts   — cohort needs track; creates ledgers at balance=250
     *  3. Courses & Groups   — need cohort + students
     *  4. Engagements & Sessions — need cohort, instructor, lab group
     *  5. Attendance         — needs delivered sessions; decrements ledger balances
     *  6. Submissions        — needs delivered lab sessions + students
     *  7. Grades             — needs courses + students
     *  8. Announcements      — needs cohort + author users
     *  9. Student Tags       — needs students + cohort + tagger
     * 10. Billing Records    — needs delivered sessions + instructors
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            TrackAndCohortSeeder::class,
            CourseAndLabGroupSeeder::class,
            EngagementAndSessionSeeder::class,
            AttendanceSeeder::class,
            SubmissionSeeder::class,
            GradeSeeder::class,
            AnnouncementSeeder::class,
            StudentTagSeeder::class,
            BillingRecordSeeder::class,
        ]);
    }
}