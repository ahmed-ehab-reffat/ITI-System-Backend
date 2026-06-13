<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AttendanceLedgerResource;
use App\Models\AttendanceLedger;
use App\Models\AttendanceRecord;
use App\Models\User;

class AttendanceLedgerController extends Controller
{
   //  Returns the student's current balance plus a full chronological history built from their attendance records.
    public function show(User $user): AttendanceLedgerResource
    {
        $this->authorize('view', [AttendanceLedger::class, $user]);

        $ledgers = AttendanceLedger::where('student_id', $user->id)
            ->with('cohort')
            ->get();

        $history = AttendanceRecord::with(['session.engagement.cohort'])
            ->where('student_id', $user->id)
            ->whereIn('status', ['absent', 'excused'])
            ->orderBy('created_at')
            ->get()
            ->map(fn ($record) => [
                'session_id'   => $record->session_id,
                'session_date' => $record->session?->session_date,
                'cohort'       => $record->session?->engagement?->cohort?->name,
                'status'       => $record->status,
                'deduction'    => $record->status === 'absent' ? -25 : -5,
                'recorded_at'  => $record->created_at,
            ]);

        return new AttendanceLedgerResource([
            'student'  => $user,
            'ledgers'  => $ledgers,
            'history'  => $history,
        ]);
    }
}