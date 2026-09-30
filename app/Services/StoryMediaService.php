<?php

namespace App\Services;

use App\Models\Story;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class StoryMediaService
{
    private const DISK = 'public';
    private const DIRECTORY = 'stories';

    public function store(UploadedFile $file): array
    {
        $mimeType = $file->getMimeType();

        $mediaType = $this->resolveMediaType(
            $mimeType
        );

        $path = $file->store(
            self::DIRECTORY,
            self::DISK
        );

        if (!$path) {
            throw new RuntimeException(
                'Failed to store story media.'
            );
        }

        $metadata = [
            'media_path' => $path,
            'media_disk' => self::DISK,
            'media_type' => $mediaType,
            'mime_type' => $mimeType,
            'media_size' => $file->getSize(),
            'width' => null,
            'height' => null,
            'duration' => null,
        ];

        if ($mediaType === Story::MEDIA_IMAGE) {
            $metadata = array_merge(
                $metadata,
                $this->getImageMetadata($file)
            );
        }

        return $metadata;
    }

    public function delete(
        string $disk,
        string $path
    ): void {
        if (Storage::disk($disk)->exists($path)) {
            Storage::disk($disk)->delete($path);
        }
    }

    private function resolveMediaType(
        string $mimeType
    ): string {
        if (str_starts_with($mimeType, 'image/')) {
            return Story::MEDIA_IMAGE;
        }

        if (str_starts_with($mimeType, 'video/')) {
            return Story::MEDIA_VIDEO;
        }

        throw new RuntimeException(
            'Unsupported story media type.'
        );
    }

    private function getImageMetadata(
        UploadedFile $file
    ): array {
        $dimensions = getimagesize(
            $file->getRealPath()
        );

        if ($dimensions === false) {
            return [
                'width' => null,
                'height' => null,
            ];
        }

        return [
            'width' => $dimensions[0],
            'height' => $dimensions[1],
        ];
    }
}