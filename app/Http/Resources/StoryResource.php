<?php

namespace App\Http\Resources;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class StoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk($this->media_disk);

        return [
            'id' => $this->id,

            'media' => [
                'url' => $disk->url(
                    $this->media_path
                ),

                'type' => $this->media_type,
                'mime_type' => $this->mime_type,
                'size' => $this->media_size,
                'width' => $this->width,
                'height' => $this->height,
                'duration' => $this->duration,
            ],

            'caption' => $this->caption,
            'visibility' => $this->visibility,

            'is_viewed' => (bool) (
                $this->is_viewed ?? false
            ),

            'views_count' => $this->whenCounted(
                'views'
            ),

            'created_at' => $this->created_at,
            'expires_at' => $this->expires_at,
        ];
    }
}