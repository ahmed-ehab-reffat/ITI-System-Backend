<?php

namespace App\Http\Requests\Cohort;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCohortRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->isBranchManager() ; // LC-2
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'track_id' => ['sometimes', 'uuid', 'exists:tracks,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'status' => ['sometimes', 'string', 'in:open,active,closed'],
            'track_admin_ids' => ['nullable', 'array'],
            'track_admin_ids.*' => [
                'uuid',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->where('role', 'track_admin');
                }),
            ],
        ];

        // Validate starts_at and ends_at
        if ($this->has('starts_at')) {
            $rules['starts_at'] = ['required', 'date'];
        }

        if ($this->has('ends_at')) {
            $cohort = $this->route('cohort');
            $startsAt = $this->input('starts_at')
                ?? ($cohort instanceof \App\Models\Cohort ? $cohort : \App\Models\Cohort::find($cohort))
                ?->starts_at
                ?->toDateString();
            if ($startsAt) {
                $rules['ends_at'] = ['required', 'date', 'after:' . $startsAt];
            } else {
                $rules['ends_at'] = ['required', 'date'];
            }
        }

        return $rules;
    }
}
