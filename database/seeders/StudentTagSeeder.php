<?php

namespace Database\Seeders;

use App\Models\Cohort;
use App\Models\StudentTag;
use App\Models\User;
use Illuminate\Database\Seeder;

class StudentTagSeeder extends Seeder
{
    public function run(): void
    {
        $webCohort    = Cohort::where('name', 'like', '%Intake 45%')->first();
        $mobileCohort = Cohort::where('name', 'like', '%Intake 12%')->first();

        $webAdmin    = User::where('email', 'admin.web@iti.test')->first();
        $mobileAdmin = User::where('email', 'admin.mobile@iti.test')->first();
        $webLabA     = User::where('email', 'tarek@iti.test')->first();
        $webLabB     = User::where('email', 'rania@iti.test')->first();
        $mobLabA     = User::where('email', 'nadia@iti.test')->first();

        $webStudents    = User::whereHas('attendanceLedger', fn ($q) => $q->where('cohort_id', $webCohort->id))->get();
        $mobileStudents = User::whereHas('attendanceLedger', fn ($q) => $q->where('cohort_id', $mobileCohort->id))->get();

        // ── Web Dev Tags ─────────────────────────────────────────────────────────
        $webTags = [
            [
                'student'   => $webStudents[0],
                'tagger'    => $webLabA,
                'tag_type'  => 'predefined',
                'tag_value' => 'uses AI',
                'note'      => 'Multiple lab submissions show heavy AI-generated code with no understanding when asked to explain. Flagged for closer review.',
            ],
            [
                'student'   => $webStudents[0],
                'tagger'    => $webAdmin,
                'tag_type'  => 'free_text',
                'tag_value' => 'At-risk — counseling scheduled',
                'note'      => 'Ledger balance dropped below 150. Attendance counseling session scheduled for next week.',
            ],
            [
                'student'   => $webStudents[1],
                'tagger'    => $webLabA,
                'tag_type'  => 'predefined',
                'tag_value' => 'loves extra work',
                'note'      => 'Consistently submits optional bonus tasks and helps peers during lab time.',
            ],
            [
                'student'   => $webStudents[2],
                'tagger'    => $webLabB,
                'tag_type'  => 'predefined',
                'tag_value' => 'Cheating',
                'note'      => 'Lab 3 submission was a verbatim copy of another student\'s repository. Score set to 0 pending review.',
            ],
            [
                'student'   => $webStudents[3],
                'tagger'    => $webAdmin,
                'tag_type'  => 'free_text',
                'tag_value' => 'Strong candidate — advanced track',
                'note'      => 'Scoring above 90 in all components. Recommended for the advanced API design elective.',
            ],
            [
                'student'   => $webStudents[5],
                'tagger'    => $webLabA,
                'tag_type'  => 'free_text',
                'tag_value' => 'Needs extra support',
                'note'      => 'Struggles with async/await concepts. Suggested additional resources and office hours.',
            ],
        ];

        foreach ($webTags as $tag) {
            StudentTag::create([
                'student_id' => $tag['student']->id,
                'tagged_by'  => $tag['tagger']->id,
                'cohort_id'  => $webCohort->id,
                'tag_type'   => $tag['tag_type'],
                'tag_value'  => $tag['tag_value'],
                'note'       => $tag['note'],
            ]);
        }

        // ── Mobile Dev Tags ──────────────────────────────────────────────────────
        $mobileTags = [
            [
                'student'   => $mobileStudents[0],
                'tagger'    => $mobLabA,
                'tag_type'  => 'predefined',
                'tag_value' => 'uses AI',
                'note'      => 'Flutter widget code is clearly AI-generated. Student cannot explain widget tree decisions when asked.',
            ],
            [
                'student'   => $mobileStudents[2],
                'tagger'    => $mobileAdmin,
                'tag_type'  => 'free_text',
                'tag_value' => 'Top performer',
                'note'      => 'Highest scorer in Dart OOP. Built a complete demo app beyond the lab requirements.',
            ],
            [
                'student'   => $mobileStudents[4],
                'tagger'    => $mobLabA,
                'tag_type'  => 'predefined',
                'tag_value' => 'loves extra work',
                'note'      => 'Submitted the optional BLoC extension task and presented it to the group voluntarily.',
            ],
        ];

        foreach ($mobileTags as $tag) {
            StudentTag::create([
                'student_id' => $tag['student']->id,
                'tagged_by'  => $tag['tagger']->id,
                'cohort_id'  => $mobileCohort->id,
                'tag_type'   => $tag['tag_type'],
                'tag_value'  => $tag['tag_value'],
                'note'       => $tag['note'],
            ]);
        }
    }
}