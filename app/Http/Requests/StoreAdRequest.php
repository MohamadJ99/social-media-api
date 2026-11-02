<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdRequest extends FormRequest
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
                'required',
                'string',
                'max:150',
            ],

            'description' => [
                'nullable',
                'string',
                'max:5000',
            ],

            // Advertiser information
            'advertiser_name' => [
                'required',
                'string',
                'max:120',
            ],

            // Advertisement images
            'image' => [
                'required',
                'image',
                'mimes:jpeg,png,webp',
                'max:5120',
            ],

            'advertiser_logo' => [
                'nullable',
                'image',
                'mimes:jpeg,png,webp',
                'max:2048',
            ],

            // Destination
            'target_url' => [
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
                'nullable',
                'date',
            ],

            'ends_at' => [
                'nullable',
                'date',
                'after_or_equal:starts_at',
            ],
        ];
    }
}