<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceLedgerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $student = $this->resource['student'];
        $ledgers = $this->resource['ledgers'];
        $history = $this->resource['history'];

        return [
            'student' => [
                'id'   => $student->id,
                'name' => $student->name,
            ],

            'total_balance' => $ledgers->sum('balance'),

            'ledgers' => $ledgers->map(fn ($ledger) => [
                'cohort_id'   => $ledger->cohort_id,
                'cohort_name' => $ledger->cohort?->name,
                'balance'     => $ledger->balance,
                'max_balance' => 250,
                'updated_at'  => $ledger->updated_at,
            ]),

            'history' => $history,
        ];
    }
}