<?php

namespace App\Http\Requests\ExcuseRequest;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ReviewExcuseRequest extends FormRequest
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
            'reviewer_note' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
