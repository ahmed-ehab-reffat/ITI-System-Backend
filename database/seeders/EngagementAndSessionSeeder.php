<?php

namespace Database\Seeders;

use App\Models\Cohort;
use App\Models\Engagement;
use App\Models\LabGroup;
use App\Models\Session as SessionModel;
use App\Models\User;
use Illuminate\Database\Seeder;

class EngagementAndSessionSeeder extends Seeder
{
    public function run(): void
    {
        $cohort     = Cohort::where('status', 'active')->first();
        $instructor = User::where('role', 'instructor')->first();
        $groupA     = LabGroup::where('cohort_id', $cohort->id)->where('name', 'Group A')->first();

        // Lecture engagement (no lab group)
        $lecture = Engagement::create([
            'cohort_id'         => $cohort->id,
            'instructor_id'     => $instructor->id,
            'lab_group_id'      => null,
            'type'              => 'lecture',
            'starts_at'         => now()->subWeeks(3),
            'ends_at'           => now()->addMonths(2),
            'hours_per_session' => 3.00,
        ]);

        // Lab engagement (tied to Group A)
        $lab = Engagement::create([
            'cohort_id'         => $cohort->id,
            'instructor_id'     => $instructor->id,
            'lab_group_id'      => $groupA->id,
            'type'              => 'lab',
            'starts_at'         => now()->subWeeks(3),
            'ends_at'           => now()->addMonths(2),
            'hours_per_session' => 2.00,
        ]);

        // Seed 3 delivered sessions for each engagement
        foreach ([$lecture, $lab] as $engagement) {
            for ($i = 0; $i < 3; $i++) {
                SessionModel::create([
                    'engagement_id' => $engagement->id,
                    'session_date'  => now()->subWeeks(3)->addDays($i * 7),
                    'is_delivered'  => true,
                ]);
            }
        }
    }
}
