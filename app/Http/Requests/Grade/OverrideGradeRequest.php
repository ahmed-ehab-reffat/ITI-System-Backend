<?php

namespace App\Http\Requests\Grade;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class OverrideGradeRequest extends FormRequest
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
            'new_value' => ['required', 'numeric', 'min:0', 'max:100'],
            'reason'    => ['required', 'string', 'max:2000'],
        ];
    }
}
