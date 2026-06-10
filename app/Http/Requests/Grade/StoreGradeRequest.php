<?php

namespace App\Http\Requests\Grade;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isTrackAdmin();
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'student_id'     => ['required', 'uuid', Rule::exists('users', 'id')->where('role', 'student')],
            'exam_raw_score' => ['required', 'numeric', 'min:0'],
            'exam_raw_max'   => ['required', 'numeric', 'min:0.01'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $score = (float) $this->input('exam_raw_score', 0);
            $max   = (float) $this->input('exam_raw_max', 0);

            if ($max > 0 && $score > $max) {
                $v->errors()->add('exam_raw_score', 'exam_raw_score cannot exceed exam_raw_max.');
            }
        });
    }
}
