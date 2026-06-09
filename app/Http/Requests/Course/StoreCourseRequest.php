<?php

namespace App\Http\Requests\Course;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCourseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->isTrackAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'lab_weight' => ['required', 'integer', 'min:0', 'max:100'],
            'exam_weight' => ['required', 'integer', 'min:0', 'max:100'],
        ];
    }

    // GRD-1: weights must sum to 100
    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            if ($this->lab_weight + $this->exam_weight !== 100) {
                $v->errors()->add('lab_weight', 'lab_weight and exam_weight must sum to 100.');
            }
        });
    }
}
