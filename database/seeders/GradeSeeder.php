<?php

namespace Database\Seeders;

use App\Models\AttendanceLedger;
use App\Models\Cohort;
use App\Models\Course;
use App\Models\CourseGrade;
use App\Models\GradeOverride;
use App\Models\User;
use Illuminate\Database\Seeder;

class GradeSeeder extends Seeder
{
    public function run(): void
    {
        $webAdmin    = User::where('email', 'admin.web@iti.test')->first();
        $mobileAdmin = User::where('email', 'admin.mobile@iti.test')->first();

        $webCohort    = Cohort::where('name', 'like', '%Intake 45%')->first();
        $mobileCohort = Cohort::where('name', 'like', '%Intake 12%')->first();

        $webCourses    = Course::where('cohort_id', $webCohort->id)->get();
        $mobileCourses = Course::where('cohort_id', $mobileCohort->id)->get();

        // Direct ledger query — no User relationship needed
        $webStudentIds    = AttendanceLedger::where('cohort_id', $webCohort->id)->pluck('student_id');
        $mobileStudentIds = AttendanceLedger::where('cohort_id', $mobileCohort->id)->pluck('student_id');

        $webStudents    = User::whereIn('id', $webStudentIds)->get();
        $mobileStudents = User::whereIn('id', $mobileStudentIds)->get();

        $this->seedCourseGrades($webCourses, $webStudents, $webAdmin);
        $this->seedCourseGrades($mobileCourses, $mobileStudents, $mobileAdmin);
    }

    private function seedCourseGrades($courses, $students, $admin): void
    {
        foreach ($courses as $courseIndex => $course) {
            foreach ($students as $studentIndex => $student) {
                $examRawMax    = 100.00;
                $examRawScore  = $this->realisticScore($studentIndex, $courseIndex);
                $examNormalized = round(($examRawScore / $examRawMax) * $course->exam_weight, 2);
                $labScore       = round((rand(60, 100) / 100) * $course->lab_weight, 2);
                $computedScore  = round($examNormalized + $labScore, 2);

                $grade = CourseGrade::create([
                    'course_id'      => $course->id,
                    'student_id'     => $student->id,
                    'exam_raw_score' => $examRawScore,
                    'exam_raw_max'   => $examRawMax,
                    'computed_score' => $computedScore,
                ]);

                // Grade override for first student in first course (GRD-6)
                if ($studentIndex === 0 && $courseIndex === 0) {
                    $originalValue = $grade->computed_score;
                    $newValue      = min($originalValue + 3.5, 100.00);

                    GradeOverride::create([
                        'course_grade_id' => $grade->id,
                        'overridden_by'   => $admin->id,
                        'original_value'  => $originalValue,
                        'new_value'       => $newValue,
                        'reason'          => 'Marking error corrected — student\'s answer on Q7 was valid but marked wrong by the original examiner.',
                    ]);

                    $grade->update(['computed_score' => $newValue]);
                }
            }
        }
    }

    private function realisticScore(int $studentIndex, int $courseIndex): float
    {
        if ($studentIndex % 8 === 0) return rand(40, 59);  // at-risk (ANL-1)
        if ($studentIndex % 5 === 0) return rand(88, 98);  // top performer
        return rand(62, 87);                                // average
    }
}