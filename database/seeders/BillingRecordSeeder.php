<?php

namespace Database\Seeders;

use App\Models\BillingRecord;
use App\Models\Session;
use Illuminate\Database\Seeder;

class BillingRecordSeeder extends Seeder
{
    public function run(): void
    {
        // Only delivered sessions generate billing records (BIL-1)
        $sessions = Session::where('is_delivered', true)
            ->with('engagement.instructor')
            ->get();

        foreach ($sessions as $session) {
            $instructor = $session->engagement->instructor;

            if (!$instructor) continue;

            // internal track admin → 'internal' | external instructor → 'external' (BIL-2)
            $personType = $instructor->compensation_type === 'internal' ? 'internal' : 'external';

            BillingRecord::firstOrCreate(
                [
                    'user_id'    => $instructor->id,
                    'session_id' => $session->id,
                ],
                [
                    'hours'       => $session->engagement->hours_per_session,
                    'person_type' => $personType,
                ]
            );
        }
    }
}