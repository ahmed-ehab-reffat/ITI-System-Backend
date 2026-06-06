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
        $cohort = Cohort::where('status', 'active')->first();

        // Create courses
        Course::create([
            'cohort_id'   => $cohort->id,
            'name'        => 'Laravel Fundamentals',
            'lab_weight'  => 40,
            'exam_weight' => 60,
        ]);

        Course::create([
            'cohort_id'   => $cohort->id,
            'name'        => 'Vue.js & Frontend',
            'lab_weight'  => 50,
            'exam_weight' => 50,
        ]);

        // Create lab groups and assign students
        $students = User::where('role', 'student')->get();
        $half = (int) ceil($students->count() / 2);

        $groupA = LabGroup::create([
            'cohort_id' => $cohort->id,
            'name'      => 'Group A',
        ]);
        $groupA->students()->attach($students->take($half)->pluck('id'));

        $groupB = LabGroup::create([
            'cohort_id' => $cohort->id,
            'name'      => 'Group B',
        ]);
        $groupB->students()->attach($students->skip($half)->pluck('id'));
    }
}
