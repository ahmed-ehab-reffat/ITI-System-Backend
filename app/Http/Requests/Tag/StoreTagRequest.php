<?php

namespace App\Http\Requests\Tag;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTagRequest extends FormRequest
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
            'tag_type'  => ['required', 'in:predefined,free_text'],
            'tag_value' => ['required', 'string', function ($attr, $value, $fail) {
                if ($this->tag_type === 'predefined') {
                    $allowed = ['uses AI', 'Cheating', 'loves extra work'];
                    if (!in_array($value, $allowed)) {
                        $fail('Predefined tag must be one of: ' . implode(', ', $allowed));
                    }
                }
            }],
            'note'      => ['nullable', 'string', 'max:500'],
        ];
    }
}
