<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'  => ['sometimes', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->user->id),
            ],
            'role'  => ['sometimes', 'string', Rule::in([
                'branch_manager',
                'track_admin',
                'instructor',
                'student',
            ])],

            'compensation_type' => [
                Rule::requiredIf(fn (): bool => in_array(
                    $this->input('role', $this->user->role),
                    ['track_admin', 'instructor'],
                    true,
                )),
                'nullable',
                'string',
                Rule::in(['internal', 'external']),
            ],

            'hourly_rate' => [
                Rule::requiredIf(fn (): bool => in_array(
                    $this->input('role', $this->user->role),
                    ['track_admin', 'instructor'],
                    true,
                )),
                'nullable',
                'numeric',
                'min:0',
            ],

            'fixed_salary' => [
                Rule::requiredIf(fn (): bool => $this->input('role', $this->user->role) === 'instructor'
                    && $this->input('compensation_type', $this->user->compensation_type) === 'internal'),
                'nullable',
                'numeric',
                'min:0',
            ],

            'expires_at' => ['nullable', 'date', 'after:now'],
        ];
    }
}
