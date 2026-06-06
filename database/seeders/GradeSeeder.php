<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseGrade;
use App\Models\GradeOverride;
use App\Models\User;
use Illuminate\Database\Seeder;

class GradeSeeder extends Seeder
{
    public function run(): void
    {
        $courses = Course::all();
        $students = User::where('role', 'student')->get();
        $admin = User::where('role', 'track_admin')->first();

        foreach ($courses as $course) {
            foreach ($students as $index => $student) {
                // Seed some raw score
                $examRawMax = 100.00;
                $examRawScore = rand(70, 95);
                $computedScore = $examRawScore; // simple mapping for seeder

                $grade = CourseGrade::create([
                    'course_id'       => $course->id,
                    'student_id'      => $student->id,
                    'exam_raw_score'  => $examRawScore,
                    'exam_raw_max'    => $examRawMax,
                    'computed_score'  => $computedScore,
                ]);

                // Create a grade override for one student on Laravel Fundamentals
                if ($index === 0 && $course->name === 'Laravel Fundamentals') {
                    $originalValue = $grade->computed_score;
                    $newValue = 98.00;

                    GradeOverride::create([
                        'course_grade_id' => $grade->id,
                        'overridden_by'   => $admin->id,
                        'original_value'  => $originalValue,
                        'new_value'       => $newValue,
                        'reason'          => 'Typo corrected in the exam sheet marking.',
                    ]);

                    // Update the grade computed score
                    $grade->update([
                        'computed_score' => $newValue,
                    ]);
                }
            }
        }
    }
}
