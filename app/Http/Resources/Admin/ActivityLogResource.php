<?php

namespace App\Http\Resources\Admin;

use App\Models\Ad;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'action' => $this->action,

            'description' => $this->description,

            'properties' => $this->properties,

            'causer' => $this->whenLoaded(
                'causer',
                fn () => $this->causer ? [
                    'id' => $this->causer->id,
                    'name' => $this->causer->name,
                    'username' => $this->causer->username,
                    'avatar' => $this->causer->avatar,
                ] : null
            ),

            'subject' => $this->subject_type ? [
                'type' => $this->subjectType(),
                'id' => $this->subject_id,
            ] : null,

            'created_at' => $this->created_at,
        ];
    }

    private function subjectType(): string
    {
        return match ($this->subject_type) {
            (new Post())->getMorphClass() => 'post',
            (new Comment())->getMorphClass() => 'comment',
            (new Report())->getMorphClass() => 'report',
            (new User())->getMorphClass() => 'user',
            (new Ad())->getMorphClass() => 'ad',
            default => 'unknown',
        };
    }
}
