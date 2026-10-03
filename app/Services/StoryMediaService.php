<?php

namespace App\Services;

use App\Models\Story;
use getID3;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class StoryMediaService
{
    private const DISK = 'public';
    private const DIRECTORY = 'stories';

    private const MAX_VIDEO_DURATION_SECONDS = 60;

    public function store(UploadedFile $file): array
    {
        $mimeType = $file->getMimeType();

        if (!$mimeType) {
            throw ValidationException::withMessages([
                'media' => [
                    'Unable to determine media type.',
                ],
            ]);
        }

        $mediaType = $this->resolveMediaType(
            $mimeType
        );

        $metadata = [
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

        if ($mediaType === Story::MEDIA_VIDEO) {
            $metadata = array_merge(
                $metadata,
                $this->getVideoMetadata($file)
            );
        }

        $path = $file->store(
            self::DIRECTORY,
            self::DISK
        );

        if (!$path) {
            throw new RuntimeException(
                'Failed to store story media.'
            );
        }

        return [
            'media_path' => $path,
            'media_disk' => self::DISK,
            ...$metadata,
        ];
    }

    public function delete(
        string $disk,
        string $path
    ): void {
        $storage = Storage::disk($disk);

        if (!$storage->exists($path)) {
            return;
        }

        if (!$storage->delete($path)) {
            throw new RuntimeException(
                'Failed to delete story media.'
            );
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

        throw ValidationException::withMessages([
            'media' => [
                'Unsupported story media type.',
            ],
        ]);
    }

    private function getImageMetadata(
        UploadedFile $file
    ): array {
        $path = $file->getRealPath();

        if (!$path) {
            throw ValidationException::withMessages([
                'media' => [
                    'Unable to access the uploaded image.',
                ],
            ]);
        }

        $dimensions = getimagesize($path);

        if ($dimensions === false) {
            throw ValidationException::withMessages([
                'media' => [
                    'Unable to read the image.',
                ],
            ]);
        }

        return [
            'width' => $dimensions[0],
            'height' => $dimensions[1],
            'duration' => null,
        ];
    }

    private function getVideoMetadata(
        UploadedFile $file
    ): array {
        $path = $file->getRealPath();

        if (!$path) {
            throw ValidationException::withMessages([
                'media' => [
                    'Unable to access the uploaded video.',
                ],
            ]);
        }

        $getID3 = new getID3();

        $info = $getID3->analyze($path);

        if (!empty($info['error'])) {
            throw ValidationException::withMessages([
                'media' => [
                    'Unable to read the video file.',
                ],
            ]);
        }

        if (!isset($info['playtime_seconds'])) {
            throw ValidationException::withMessages([
                'media' => [
                    'Unable to determine video duration.',
                ],
            ]);
        }

        $duration = (int) ceil(
            (float) $info['playtime_seconds']
        );

        if (
            $duration >
            self::MAX_VIDEO_DURATION_SECONDS
        ) {
            throw ValidationException::withMessages([
                'media' => [
                    'Videos must not exceed 60 seconds.',
                ],
            ]);
        }

        return [
            'width' => isset(
                $info['video']['resolution_x']
            )
                ? (int) $info['video']['resolution_x']
                : null,

            'height' => isset(
                $info['video']['resolution_y']
            )
                ? (int) $info['video']['resolution_y']
                : null,

            'duration' => $duration,
        ];
    }
}