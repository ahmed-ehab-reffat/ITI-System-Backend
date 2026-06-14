<?php

namespace App\Services;

use App\Models\AttendanceLedger;
use App\Models\AttendanceRecord;
use App\Models\User;

class AttendanceLedgerService
{
    
    public function initialise(User $student, string $cohortId): AttendanceLedger
    {
        return AttendanceLedger::firstOrCreate(
            ['student_id' => $student->id, 'cohort_id' => $cohortId],
            ['balance'    => config('attendance.starting_balance')],
        );
    }

    public function deduct(User $student, string $cohortId, string $status): void
    {
        $amount = $this->deductionFor($status);

        if ($amount === 0) {
            return; 
        }
        
        if ($amount < 0) {
            $amount = -$amount; 
        }

        $ledger = $this->ledgerFor($student, $cohortId);
        $newBalance = max(0, $ledger->balance - $amount);
        $ledger->update(['balance' => $newBalance]);
    }

    public function adjustForStatusChange(
        AttendanceRecord $record,
        string $oldStatus,
        string $newStatus,
        string $cohortId,
    ): void {
        if ($oldStatus === $newStatus) {
            return;
        }

        $ledger = $this->ledgerFor($record->student, $cohortId);
        $diff = $this->deductionFor($oldStatus) - $this->deductionFor($newStatus);
        
        $maxBalance = config('attendance.starting_balance', 250);
        $newBalance = min($maxBalance, max(0, $ledger->balance + $diff));
        $ledger->update(['balance' => $newBalance]);
    }

    public function balance(User $student, string $cohortId): int
    {
        return $this->ledgerFor($student, $cohortId)->balance;
    }

    public function credit(User $student, string $cohortId, int $amount): void
    {
        if ($amount === 0) {
            return;
        }

        $ledger = $this->ledgerFor($student, $cohortId);
        $maxBalance = config('attendance.starting_balance', 250);
        $newBalance = min($maxBalance, max(0, $ledger->balance + $amount));
        $ledger->update(['balance' => $newBalance]);
    }

    private function deductionFor(string $status): int
    {
        return match ($status) {
            'absent'  => config('attendance.unexcused_deduction') ,
            'excused' => config('attendance.excused_deduction') ,
            default   => 0,
        };
    }

    private function ledgerFor(User $student, string $cohortId): AttendanceLedger
    {
        return AttendanceLedger::firstOrCreate(
            ['student_id' => $student->id, 'cohort_id' => $cohortId],
            ['balance'    => config('attendance.starting_balance')],
        );
    }
}