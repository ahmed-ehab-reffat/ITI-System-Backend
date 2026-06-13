<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
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
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'role'  => ['required', 'string', Rule::in([
                'branch_manager',
                'track_admin',
                'instructor',
                'student',
            ])],

            'compensation_type' => [
                Rule::requiredIf(fn (): bool => in_array(
                    $this->input('role'),
                    ['track_admin', 'instructor'],
                    true,
                )),
                'nullable',
                'string',
                Rule::in(['internal', 'external']),
            ],

            'hourly_rate' => [
                Rule::requiredIf(fn (): bool => in_array(
                    $this->input('role'),
                    ['track_admin', 'instructor'],
                    true,
                )),
                'nullable',
                'numeric',
                'min:0',
            ],

            'fixed_salary' => [
                Rule::requiredIf(fn (): bool => $this->input('role') === 'instructor'
                    && $this->input('compensation_type') === 'internal'),
                'nullable',
                'numeric',
                'min:0',
            ],

            'expires_at' => ['nullable', 'date', 'after:now'],
        ];
    }
}
