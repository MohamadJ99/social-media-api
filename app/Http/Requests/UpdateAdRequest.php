<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAdRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Advertisement content
            'title' => [
                'sometimes',
                'required',
                'string',
                'max:150',
            ],

            'description' => [
                'sometimes',
                'nullable',
                'string',
                'max:5000',
            ],

            // Advertiser information
            'advertiser_name' => [
                'sometimes',
                'required',
                'string',
                'max:120',
            ],

            // Advertisement images
            'image' => [
                'sometimes',
                'required',
                'image',
                'mimes:jpeg,png,webp',
                'max:5120',
            ],

            'advertiser_logo' => [
                'sometimes',
                'nullable',
                'image',
                'mimes:jpeg,png,webp',
                'max:2048',
            ],

            // Destination
            'target_url' => [
                'sometimes',
                'required',
                'url',
                'max:2048',
            ],

            // Status
            'is_active' => [
                'sometimes',
                'boolean',
            ],

            // Scheduling
            'starts_at' => [
                'sometimes',
                'nullable',
                'date',
            ],

            'ends_at' => [
                'sometimes',
                'nullable',
                'date',
                'after_or_equal:starts_at',
            ],
        ];
    }
}