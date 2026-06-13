<?php

namespace Database\Seeders;

use App\Models\Cohort;
use App\Models\Engagement;
use App\Models\LabGroup;
use App\Models\Session as SessionModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class EngagementAndSessionSeeder extends Seeder
{
    /**
     * Generate weekly session dates for an engagement.
     * Past sessions (before today) are marked delivered.
     * Future sessions are not yet delivered.
     *
     * @param  Carbon  $cohortStart
     * @param  int     $weeklyDayOfWeek  Carbon::SUNDAY, MONDAY ... SATURDAY
     * @param  int     $totalWeeks
     * @return array   [['date' => Carbon, 'delivered' => bool], ...]
     */
    private function weeklyDates(Carbon $cohortStart, int $weeklyDayOfWeek, int $totalWeeks): array
    {
        $dates = [];
        // Start from the first occurrence of the target weekday on or after cohort start
        $firstDate = (clone $cohortStart)->next($weeklyDayOfWeek);
        if ($firstDate->lt($cohortStart)) {
            $firstDate->addWeek();
        }

        for ($week = 0; $week < $totalWeeks; $week++) {
            $date = (clone $firstDate)->addWeeks($week);
            $dates[] = [
                'date'      => $date,
                'delivered' => $date->isPast(),
            ];
        }

        return $dates;
    }

    public function run(): void
    {
        $today = now();

        // ── Cohorts & Instructors ────────────────────────────────────────────────
        $webCohort    = Cohort::where('name', 'like', '%Intake 45%')->first();
        $mobileCohort = Cohort::where('name', 'like', '%Intake 12%')->first();

        // Web Dev instructors
        $webLecturer  = User::where('email', 'hossam@iti.test')->first();
        $webLabA      = User::where('email', 'tarek@iti.test')->first();
        $webLabB      = User::where('email', 'rania@iti.test')->first();
        $webLabC      = User::where('email', 'sherif@iti.test')->first();

        // Mobile Dev instructors
        $mobLecturer  = User::where('email', 'amal@iti.test')->first();
        $mobLabA      = User::where('email', 'nadia@iti.test')->first();
        $mobLabB      = User::where('email', 'bassem@iti.test')->first();

        // Lab groups
        $webGroupA = LabGroup::where('cohort_id', $webCohort->id)->where('name', 'Group A')->first();
        $webGroupB = LabGroup::where('cohort_id', $webCohort->id)->where('name', 'Group B')->first();
        $webGroupC = LabGroup::where('cohort_id', $webCohort->id)->where('name', 'Group C')->first();

        $mobGroupA = LabGroup::where('cohort_id', $mobileCohort->id)->where('name', 'Group A')->first();
        $mobGroupB = LabGroup::where('cohort_id', $mobileCohort->id)->where('name', 'Group B')->first();

        // ════════════════════════════════════════════════════════════════════════
        // WEB DEV COHORT — starts 10 weeks ago
        // ════════════════════════════════════════════════════════════════════════
        $webStart = (clone $today)->subWeeks(10)->startOfDay();

        // ── WD-1: Lecture (Sundays, 3h, whole cohort) ───────────────────────────
        $wdLecture = Engagement::create([
            'cohort_id'         => $webCohort->id,
            'instructor_id'     => $webLecturer->id,
            'lab_group_id'      => null,
            'type'              => 'lecture',
            'starts_at'         => $webStart,
            'ends_at'           => $webStart->copy()->addWeeks(20),
            'hours_per_session' => 3.00,
        ]);

        foreach ($this->weeklyDates($webStart, Carbon::SUNDAY, 20) as $s) {
            SessionModel::create([
                'engagement_id' => $wdLecture->id,
                'session_date'  => $s['date'],
                'is_delivered'  => $s['delivered'],
            ]);
        }

        // ── WD-2: Lab Group A (Mondays, 2h) ─────────────────────────────────────
        $wdLabA = Engagement::create([
            'cohort_id'         => $webCohort->id,
            'instructor_id'     => $webLabA->id,
            'lab_group_id'      => $webGroupA->id,
            'type'              => 'lab',
            'starts_at'         => $webStart,
            'ends_at'           => $webStart->copy()->addWeeks(20),
            'hours_per_session' => 2.00,
        ]);

        foreach ($this->weeklyDates($webStart, Carbon::MONDAY, 20) as $s) {
            SessionModel::create([
                'engagement_id' => $wdLabA->id,
                'session_date'  => $s['date'],
                'is_delivered'  => $s['delivered'],
            ]);
        }

        // ── WD-3: Lab Group B (Wednesdays, 2h) ──────────────────────────────────
        $wdLabB = Engagement::create([
            'cohort_id'         => $webCohort->id,
            'instructor_id'     => $webLabB->id,
            'lab_group_id'      => $webGroupB->id,
            'type'              => 'lab',
            'starts_at'         => $webStart,
            'ends_at'           => $webStart->copy()->addWeeks(20),
            'hours_per_session' => 2.00,
        ]);

        foreach ($this->weeklyDates($webStart, Carbon::WEDNESDAY, 20) as $s) {
            SessionModel::create([
                'engagement_id' => $wdLabB->id,
                'session_date'  => $s['date'],
                'is_delivered'  => $s['delivered'],
            ]);
        }

        // ── WD-4: Lab Group C (Thursdays, 2h) ───────────────────────────────────
        $wdLabC = Engagement::create([
            'cohort_id'         => $webCohort->id,
            'instructor_id'     => $webLabC->id,
            'lab_group_id'      => $webGroupC->id,
            'type'              => 'lab',
            'starts_at'         => $webStart,
            'ends_at'           => $webStart->copy()->addWeeks(20),
            'hours_per_session' => 2.00,
        ]);

        foreach ($this->weeklyDates($webStart, Carbon::THURSDAY, 20) as $s) {
            SessionModel::create([
                'engagement_id' => $wdLabC->id,
                'session_date'  => $s['date'],
                'is_delivered'  => $s['delivered'],
            ]);
        }

        // ════════════════════════════════════════════════════════════════════════
        // MOBILE DEV COHORT — starts 8 weeks ago
        // ════════════════════════════════════════════════════════════════════════
        $mobStart = (clone $today)->subWeeks(8)->startOfDay();

        // ── MD-1: Lecture (Sundays, 3h, whole cohort) ───────────────────────────
        $mdLecture = Engagement::create([
            'cohort_id'         => $mobileCohort->id,
            'instructor_id'     => $mobLecturer->id,
            'lab_group_id'      => null,
            'type'              => 'lecture',
            'starts_at'         => $mobStart,
            'ends_at'           => $mobStart->copy()->addWeeks(22),
            'hours_per_session' => 3.00,
        ]);

        foreach ($this->weeklyDates($mobStart, Carbon::SUNDAY, 22) as $s) {
            SessionModel::create([
                'engagement_id' => $mdLecture->id,
                'session_date'  => $s['date'],
                'is_delivered'  => $s['delivered'],
            ]);
        }

        // ── MD-2: Lab Group A (Tuesdays, 2h) ────────────────────────────────────
        $mdLabA = Engagement::create([
            'cohort_id'         => $mobileCohort->id,
            'instructor_id'     => $mobLabA->id,
            'lab_group_id'      => $mobGroupA->id,
            'type'              => 'lab',
            'starts_at'         => $mobStart,
            'ends_at'           => $mobStart->copy()->addWeeks(22),
            'hours_per_session' => 2.00,
        ]);

        foreach ($this->weeklyDates($mobStart, Carbon::TUESDAY, 22) as $s) {
            SessionModel::create([
                'engagement_id' => $mdLabA->id,
                'session_date'  => $s['date'],
                'is_delivered'  => $s['delivered'],
            ]);
        }

        // ── MD-3: Lab Group B (Saturdays, 2h) ───────────────────────────────────
        $mdLabB = Engagement::create([
            'cohort_id'         => $mobileCohort->id,
            'instructor_id'     => $mobLabB->id,
            'lab_group_id'      => $mobGroupB->id,
            'type'              => 'lab',
            'starts_at'         => $mobStart,
            'ends_at'           => $mobStart->copy()->addWeeks(22),
            'hours_per_session' => 2.00,
        ]);

        foreach ($this->weeklyDates($mobStart, Carbon::SATURDAY, 22) as $s) {
            SessionModel::create([
                'engagement_id' => $mdLabB->id,
                'session_date'  => $s['date'],
                'is_delivered'  => $s['delivered'],
            ]);
        }

        // ════════════════════════════════════════════════════════════════════════
        // CROSS-TRACK BUSINESS SESSIONS (ATT-3, Section 4.1)
        // Both cohorts attend the same sessions — one engagement per cohort,
        // same dates, same instructor (web lecturer hosts, mobile attends too).
        // Monthly on the first Friday of the month (3 sessions).
        // ════════════════════════════════════════════════════════════════════════
        $businessDates = [
            (clone $today)->subMonths(2)->firstOfMonth()->next(Carbon::FRIDAY),
            (clone $today)->subMonth()->firstOfMonth()->next(Carbon::FRIDAY),
            (clone $today)->firstOfMonth()->next(Carbon::FRIDAY),
        ];

        // Web cohort business engagement (instructor = web lecturer)
        $wdBusiness = Engagement::create([
            'cohort_id'         => $webCohort->id,
            'instructor_id'     => $webLecturer->id,
            'lab_group_id'      => null,
            'type'              => 'business_session',
            'starts_at'         => $businessDates[0],
            'ends_at'           => $businessDates[2]->copy()->addDay(),
            'hours_per_session' => 2.00,
        ]);

        // Mobile cohort business engagement (same sessions, tracked separately per ATT-3)
        $mdBusiness = Engagement::create([
            'cohort_id'         => $mobileCohort->id,
            'instructor_id'     => $webLecturer->id,
            'lab_group_id'      => null,
            'type'              => 'business_session',
            'starts_at'         => $businessDates[0],
            'ends_at'           => $businessDates[2]->copy()->addDay(),
            'hours_per_session' => 2.00,
        ]);

        foreach ($businessDates as $date) {
            $delivered = $date->isPast();

            SessionModel::create([
                'engagement_id' => $wdBusiness->id,
                'session_date'  => $date,
                'is_delivered'  => $delivered,
            ]);

            SessionModel::create([
                'engagement_id' => $mdBusiness->id,
                'session_date'  => $date,
                'is_delivered'  => $delivered,
            ]);
        }
    }
}