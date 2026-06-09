<?php

namespace App\Http\Requests\Course;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCourseRequest extends FormRequest
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
            'name' => ['sometimes', 'string', 'max:255'],
            'lab_weight' => ['sometimes', 'integer', 'min:0', 'max:100'],
            'exam_weight' => ['sometimes', 'integer', 'min:0', 'max:100'],
        ];
    }

    // GRD-1: weights must sum to 100 (re-check on partial update)
    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $course = $this->route('course');

            $lab = $this->has('lab_weight')
                ? (int) $this->input('lab_weight')
                : (int) $course->lab_weight;

            $exam = $this->has('exam_weight')
                ? (int) $this->input('exam_weight')
                : (int) $course->exam_weight;

            if ($lab + $exam !== 100) {
                $v->errors()->add('lab_weight', 'lab_weight and exam_weight must sum to 100.');
            }
        });
    }
}
