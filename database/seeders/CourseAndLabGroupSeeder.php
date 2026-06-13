<?php

namespace Database\Seeders;

use App\Models\Cohort;
use App\Models\Course;
use App\Models\LabGroup;
use App\Models\User;
use Illuminate\Database\Seeder;

class CourseAndLabGroupSeeder extends Seeder
{
    public function run(): void
    {
        $webCohort    = Cohort::where('name', 'like', '%Intake 45%')->first();
        $mobileCohort = Cohort::where('name', 'like', '%Intake 12%')->first();

        // ── Web Dev Courses ──────────────────────────────────────────────────────
        Course::create(['cohort_id' => $webCohort->id, 'name' => 'Laravel & PHP Fundamentals', 'lab_weight' => 40, 'exam_weight' => 60]);
        Course::create(['cohort_id' => $webCohort->id, 'name' => 'Vue.js & Frontend Development', 'lab_weight' => 50, 'exam_weight' => 50]);
        Course::create(['cohort_id' => $webCohort->id, 'name' => 'Database Design & REST APIs',  'lab_weight' => 40, 'exam_weight' => 60]);

        // ── Mobile Dev Courses ───────────────────────────────────────────────────
        Course::create(['cohort_id' => $mobileCohort->id, 'name' => 'Flutter Fundamentals',    'lab_weight' => 40, 'exam_weight' => 60]);
        Course::create(['cohort_id' => $mobileCohort->id, 'name' => 'Dart & OOP Concepts',     'lab_weight' => 50, 'exam_weight' => 50]);
        Course::create(['cohort_id' => $mobileCohort->id, 'name' => 'Mobile UI/UX Patterns',   'lab_weight' => 30, 'exam_weight' => 70]);

        // ── Web Dev Lab Groups (3 × 10 students) ────────────────────────────────
        $webStudents = User::where('role', 'student')
            ->orderBy('created_at')
            ->take(30)
            ->get()
            ->chunk(10);   // [Group A: 0-9, Group B: 10-19, Group C: 20-29]

        $webGroupNames = ['Group A', 'Group B', 'Group C'];
        $webGroups     = [];

        foreach ($webGroupNames as $i => $name) {
            $group = LabGroup::create(['cohort_id' => $webCohort->id, 'name' => $name]);
            $group->students()->attach($webStudents[$i]->pluck('id'));
            $webGroups[] = $group;
        }

        // ── Mobile Dev Lab Groups (2 × 8 students) ──────────────────────────────
        $mobileStudents = User::where('role', 'student')
            ->orderBy('created_at')
            ->skip(30)
            ->take(16)
            ->get()
            ->chunk(8);    // [Group A: 0-7, Group B: 8-15]

        $mobileGroupNames = ['Group A', 'Group B'];

        foreach ($mobileGroupNames as $i => $name) {
            $group = LabGroup::create(['cohort_id' => $mobileCohort->id, 'name' => $name]);
            $group->students()->attach($mobileStudents[$i]->pluck('id'));
        }
    }
}