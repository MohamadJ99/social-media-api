<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexActivityLogsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'action' => [
                'sometimes',
                'nullable',
                'string',
                'max:100',
            ],

            'causer_id' => [
                'sometimes',
                'nullable',
                'integer',
                'exists:users,id',
            ],

            'subject_type' => [
                'sometimes',
                'nullable',
                Rule::in([
                    'post',
                    'comment',
                    'report',
                    'user',
                    'ad',
                ]),
            ],

            'search' => [
                'sometimes',
                'nullable',
                'string',
                'max:100',
            ],

            'from' => [
                'sometimes',
                'nullable',
                'date',
            ],

            'to' => [
                'sometimes',
                'nullable',
                'date',
                'after_or_equal:from',
            ],

            'per_page' => [
                'sometimes',
                'integer',
                'min:1',
                'max:50',
            ],
        ];
    }
}
