<?php

namespace App\Http\Requests\Engagement;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEngagementRequest extends FormRequest
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
                'sometimes', 'uuid',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->where('role', 'instructor');
                }),
            ],
            'type' => ['sometimes', 'string', 'in:lecture,lab'],
            'lab_group_id' => [
                'nullable', 'uuid',
                Rule::exists('lab_groups', 'id'),
            ],
            'starts_at' => ['sometimes', 'date'],
            'ends_at' => ['sometimes', 'date', 'after:starts_at'],
            'hours_per_session' => ['sometimes', 'numeric', 'min:0.5', 'max:12'],
        ];
    }

    // ENG-3: lab_group_id is required when type is lab (re-check on partial update)
    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $engagement = $this->route('engagement');

            $type = $this->input('type', $engagement->type);
            $labGroupId = $this->has('lab_group_id')
                ? $this->input('lab_group_id')
                : $engagement->lab_group_id;

            if ($type === 'lab' && empty($labGroupId)) {
                $v->errors()->add('lab_group_id', 'lab_group_id is required when type is lab.');
            }
        });
    }
}
