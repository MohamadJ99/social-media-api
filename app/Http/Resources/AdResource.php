<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class AdResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var \Illuminate\Filesystem\FilesystemAdapter $publicDisk */
        $publicDisk = Storage::disk('public');

        return [
            'id' => $this->id,

            // Advertisement content
            'title' => $this->title,
            'description' => $this->description,

            // Advertiser information
            'advertiser_name' => $this->advertiser_name,

            'advertiser_logo_url' => $this->advertiser_logo_path
                ? $publicDisk->url($this->advertiser_logo_path)
                : null,

            // Advertisement media
            'image_url' => $publicDisk->url(
                $this->image_path
            ),

            'target_url' => $this->target_url,

            // Status and scheduling
            'is_active' => $this->is_active,

            'starts_at' => $this->starts_at?->toISOString(),
            'ends_at' => $this->ends_at?->toISOString(),

            // Timestamps
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}