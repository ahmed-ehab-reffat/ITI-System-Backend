<?php

namespace App\Http\Requests\Engagement;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEngagementRequest extends FormRequest
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
            'instructor_id' => [
                'required', 'uuid',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->where('role', 'instructor');
                }),
            ],
            'type' => ['required', 'string', 'in:lecture,lab'],
            'lab_group_id' => [
                'nullable', 'uuid',
                Rule::exists('lab_groups', 'id'),
            ],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'hours_per_session' => ['required', 'numeric', 'min:0.5', 'max:12'],
        ];
    }

    // ENG-3: lab_group_id is required when type is lab
    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            if ($this->input('type') === 'lab' && empty($this->input('lab_group_id'))) {
                $v->errors()->add('lab_group_id', 'lab_group_id is required when type is lab.');
            }
        });
    }
}
