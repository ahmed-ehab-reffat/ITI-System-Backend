<?php

namespace App\Http\Requests\Cohort;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCohortRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->isBranchManager(); // LC-2
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'track_id' => ['required', 'uuid', 'exists:tracks,id'],
            'name' => ['required', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'track_admin_ids' => ['nullable', 'array'],
            'track_admin_ids.*' => ['uuid', 'exists:users,id'],
        ];
    }
}
