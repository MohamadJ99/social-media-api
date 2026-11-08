<?php

namespace App\Http\Requests\Admin;

use App\Enums\ReportReason;
use App\Enums\ReportStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexReportsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => [
                'sometimes',
                'nullable',
                Rule::enum(ReportStatus::class),
            ],

            'reason' => [
                'sometimes',
                'nullable',
                Rule::enum(ReportReason::class),
            ],

            'type' => [
                'sometimes',
                'nullable',
                Rule::in(['post', 'comment']),
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
