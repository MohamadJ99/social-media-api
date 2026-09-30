<?php

namespace App\Http\Requests;

use App\Models\Story;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'media' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,webp,mp4,mov,webm',
                'max:51200', // 50 MB maximum
            ],

            'caption' => [
                'nullable',
                'string',
                'max:500',
            ],

            'visibility' => [
                'nullable',
                Rule::in([
                    Story::VISIBILITY_PUBLIC,
                    Story::VISIBILITY_FRIENDS,
                ]),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'media.required' => 'Please select an image or video.',
            'media.mimes' => 'The story must be an image or supported video.',
            'media.max' => 'The story file must not exceed 50 MB.',
            'caption.max' => 'The caption must not exceed 500 characters.',
            'visibility.in' => 'Invalid story visibility.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if (!$this->filled('visibility')) {
            $this->merge([
                'visibility' => Story::VISIBILITY_FRIENDS,
            ]);
        }
    }

    public function after(): array
{
    return [
        function ($validator) {
            $file = $this->file('media');

            if (!$file || !$file->isValid()) {
                return;
            }

            $mimeType = $file->getMimeType();

            $isImage = str_starts_with(
                $mimeType,
                'image/'
            );

            $isVideo = str_starts_with(
                $mimeType,
                'video/'
            );

            if ($isImage && $file->getSize() > 10 * 1024 * 1024) {
                $validator->errors()->add(
                    'media',
                    'Images must not exceed 10 MB.'
                );
            }

            if ($isVideo && $file->getSize() > 50 * 1024 * 1024) {
                $validator->errors()->add(
                    'media',
                    'Videos must not exceed 50 MB.'
                );
            }
        },
    ];
}

}