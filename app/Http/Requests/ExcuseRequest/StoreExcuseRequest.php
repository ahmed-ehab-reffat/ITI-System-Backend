<?php

namespace App\Http\Requests\ExcuseRequest;

use App\Models\AttendanceRecord;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreExcuseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isStudent();
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'attendance_record_id' => ['required', 'uuid', 'exists:attendance_records,id'],
            'reason'               => ['required', 'string', 'max:2000'],
            // EXC-2: max 1MB, PDF or image
            'attachment'           => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:1024'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $record = AttendanceRecord::find($this->input('attendance_record_id'));

            if (! $record) {
                return;
            }

            if ($record->student_id !== $this->user()->id) {
                $v->errors()->add('attendance_record_id', 'This attendance record does not belong to you.');
            }

            if ($record->status !== 'absent') {
                $v->errors()->add('attendance_record_id', 'Excuse requests can only be submitted for absent records.');
            }

            if ($record->excuseRequest()->exists()) {
                $v->errors()->add('attendance_record_id', 'An excuse request already exists for this record.');
            }
        });
    }
}
