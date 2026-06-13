<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Cohort;
use App\Models\User;
use Illuminate\Database\Seeder;

class AnnouncementSeeder extends Seeder
{
    public function run(): void
    {
        $webCohort    = Cohort::where('name', 'like', '%Intake 45%')->first();
        $mobileCohort = Cohort::where('name', 'like', '%Intake 12%')->first();

        $webAdmin     = User::where('email', 'admin.web@iti.test')->first();
        $mobileAdmin  = User::where('email', 'admin.mobile@iti.test')->first();
        $webLecturer  = User::where('email', 'hossam@iti.test')->first();
        $mobLecturer  = User::where('email', 'amal@iti.test')->first();
        $webLabA      = User::where('email', 'tarek@iti.test')->first();

        $announcements = [
            // ── Web Dev announcements ────────────────────────────────────────────
            [
                'author_id'  => $webAdmin->id,
                'cohort_id'  => $webCohort->id,
                'title'      => 'Welcome to Intake 45 — Web Development',
                'body'       => "Welcome everyone to Intake 45!\n\nYour journey through Laravel, Vue.js, and REST APIs starts this Sunday. Please make sure you have your development environment set up before the first session — setup guide is pinned in the resources section.\n\nLook forward to a great cohort!\n\nAhmed Nour\nTrack Admin — Web Development",
                'created_at' => now()->subWeeks(10),
            ],
            [
                'author_id'  => $webAdmin->id,
                'cohort_id'  => $webCohort->id,
                'title'      => 'Mid-Term Grading Window — Oct 6–10',
                'body'       => "Dear students,\n\nThe mid-term grading window opens Monday, October 6th and closes Friday, October 10th at 11:59 PM. All outstanding assignments must be submitted via the platform before the deadline.\n\nGrade appeals may be filed within 48 hours of grade release. Contact your lab instructor first before escalating to the track admin.\n\nBest of luck,\nAhmed Nour",
                'created_at' => now()->subWeeks(5),
            ],
            [
                'author_id'  => $webAdmin->id,
                'cohort_id'  => $webCohort->id,
                'title'      => 'Platform Maintenance — This Saturday 2–4 AM',
                'body'       => "Hi everyone,\n\nThe platform will be down for scheduled maintenance this Saturday from 2:00 AM to 4:00 AM EET. The QR attendance system and submission portal will be unavailable during this window.\n\nAny deadlines falling within the window will be automatically extended by 2 hours. No action needed on your end.\n\nThank you,\nITI Platform Team",
                'created_at' => now()->subWeeks(3),
            ],
            [
                'author_id'  => $webLecturer->id,
                'cohort_id'  => $webCohort->id,
                'title'      => 'Lecture Recording — Session 7 Now Available',
                'body'       => "Hi everyone,\n\nThe recording for Session 7 (Laravel Middleware & Authentication) is now available in the resources section. Timestamps:\n\n00:00 — Recap of session 6\n12:30 — Middleware deep dive\n45:00 — Sanctum token authentication\n1:15:00 — Q&A\n\nPlease review before Sunday's session as we'll be building on these concepts directly.\n\nDr. Hossam",
                'created_at' => now()->subWeeks(2),
            ],
            [
                'author_id'  => $webLabA->id,
                'cohort_id'  => $webCohort->id,
                'title'      => 'Lab A — Deliverable Deadline Extended to Friday',
                'body'       => "Hi Group A,\n\nDue to the platform maintenance window last Saturday I am extending the Lab 6 deliverable deadline to this Friday at 11:00 PM.\n\nIf you have already submitted, your submission stands — no action needed. Late-penalty calculations will be based on the new deadline, not the original one.\n\nGood luck!\nEng. Tarek",
                'created_at' => now()->subDays(4),
            ],
            [
                'author_id'  => $webAdmin->id,
                'cohort_id'  => $webCohort->id,
                'title'      => 'Business Session This Friday — Attendance Mandatory',
                'body'       => "Dear students,\n\nReminder: the cross-track business session is scheduled for this Friday. Attendance is recorded and affects your ledger balance.\n\nThe session will cover industry best practices in agile delivery and client communication. Both Web Dev and Mobile Dev cohorts will attend together.\n\nVenue: Main Hall — 10:00 AM sharp.\n\nAhmed Nour",
                'created_at' => now()->subDays(2),
            ],

            // ── Mobile Dev announcements ─────────────────────────────────────────
            [
                'author_id'  => $mobileAdmin->id,
                'cohort_id'  => $mobileCohort->id,
                'title'      => 'Welcome to Intake 12 — Mobile Development',
                'body'       => "Welcome to Intake 12!\n\nWe kick off this Sunday with Dart fundamentals. Please install Flutter 3.x and Android Studio (or Xcode for Mac users) before the first session. The setup checklist is in the resources section.\n\nExcited to have you all!\n\nHeba Mansour\nTrack Admin — Mobile Development",
                'created_at' => now()->subWeeks(8),
            ],
            [
                'author_id'  => $mobileAdmin->id,
                'cohort_id'  => $mobileCohort->id,
                'title'      => 'Flutter 3.22 Upgrade — Please Update Before Sunday',
                'body'       => "Hi everyone,\n\nFlutter 3.22 was released this week and our upcoming sessions depend on features in this version. Please run `flutter upgrade` before Sunday's lecture.\n\nIf you hit any issues during the upgrade, post in the cohort forum or reach out to your lab instructor before the session.\n\nHeba Mansour",
                'created_at' => now()->subWeeks(4),
            ],
            [
                'author_id'  => $mobLecturer->id,
                'cohort_id'  => $mobileCohort->id,
                'title'      => 'State Management — Riverpod vs BLoC Comparison',
                'body'       => "Hi everyone,\n\nSeveral students asked about choosing between Riverpod and BLoC for the final project. I've written up a short comparison based on what we've covered so far:\n\n• BLoC: best for large teams and strict separation of concerns\n• Riverpod: lighter, great for medium-sized apps, less boilerplate\n\nFor the final project, either is acceptable. I'll dedicate the first 20 minutes of Sunday's session to Q&A on this.\n\nDr. Amal",
                'created_at' => now()->subWeeks(1),
            ],
            [
                'author_id'  => $mobileAdmin->id,
                'cohort_id'  => $mobileCohort->id,
                'title'      => 'Final Project Brief Released — Review Before Next Session',
                'body'       => "Hi Intake 12,\n\nThe final project brief has been released in the resources section. Key points:\n\n• Teams of 2–3 students\n• Must use Flutter + a REST backend (any language)\n• Submission deadline: 6 weeks from today\n• Presentations: last week of the cohort\n\nTeam assignments are due by next Wednesday. If you haven't found a team yet, contact me directly.\n\nHeba Mansour",
                'created_at' => now()->subDays(1),
            ],
        ];

        foreach ($announcements as $data) {
            Announcement::create($data);
        }
    }
}