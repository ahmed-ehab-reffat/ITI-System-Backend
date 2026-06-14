<?php

namespace App\Http\Requests\Submission;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isStudent();
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'url' => ['nullable', 'url', 'max:1024', 'required_without:file'],
            'file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,gif,bmp,svg', 'max:1024', 'required_without:url'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $hasUrl = $this->filled('url');
            $hasFile = $this->hasFile('file');

            if ($hasUrl && $hasFile) {
                $v->errors()->add('url', 'Provide either url or file, not both.');
            }

            if (! $hasUrl && ! $hasFile) {
                $v->errors()->add('url', 'Either url or file is required.');
            }

            $session = $this->route('session');

            if (! $session) {
                return;
            }

            $engagement = $session->engagement;

            if ($engagement->type === 'lab' && $engagement->lab_group_id) {
                $inGroup = $this->user()->labGroups()
                    ->where('lab_groups.id', $engagement->lab_group_id)
                    ->exists();

                if (! $inGroup) {
                    $v->errors()->add('url', 'You are not assigned to this lab group.');
                }
            }
        });
    }
}
