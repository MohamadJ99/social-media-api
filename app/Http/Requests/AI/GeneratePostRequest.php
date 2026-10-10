<?php

namespace App\Http\Requests\AI;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GeneratePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'topic' => [
                'required',
                'string',
                'max:500',
            ],

            'tone' => [
                'required',
                'string',
                Rule::in([
                    'professional',
                    'casual',
                    'friendly',
                    'educational',
                ]),
            ],

            'language' => [
                'required',
                'string',
                Rule::in([
                    'english',
                    'arabic',
                ]),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'topic.required' =>
                'Please enter a topic for your post.',

            'topic.max' =>
                'The topic may not be greater than 500 characters.',

            'tone.required' =>
                'Please select a tone.',

            'tone.in' =>
                'The selected tone is invalid.',

            'language.required' =>
                'Please select a language.',

            'language.in' =>
                'The selected language is invalid.',
        ];
    }
}