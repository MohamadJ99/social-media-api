<?php

namespace App\Http\Requests;

use App\Enums\ReportReason;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'reason' => [
                'required',
                Rule::enum(ReportReason::class),
            ],

            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }
}
