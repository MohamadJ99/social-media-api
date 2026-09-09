<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'bio' => $this->bio,
            'avatar' => $this->avatar,
            'cover_image' => $this->cover_image,
            'posts_count' => $this->whenCounted('posts'),
            'friends_count' => $this->friends_count,
            'friendship' => $this->friendship
                ? [
                    'id' => $this->friendship->id,
                    'status' => $this->friendship->status,
                ]
                : null,
        ];
    }
}
