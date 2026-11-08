<?php

namespace App\Http\Resources\Admin;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'reason' => $this->reason->value,

            'description' => $this->description,

            'status' => $this->status->value,

            'created_at' => $this->created_at,

            'updated_at' => $this->updated_at,

            'reporter' => $this->whenLoaded(
                'user',
                fn () => $this->user ? [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'username' => $this->user->username,
                    'avatar' => $this->user->avatar,
                ] : null
            ),

            'reportable' => $this->whenLoaded(
                'reportable',
                function () {
                    $content = $this->reportable;

                    if (!$content) {
                        return null;
                    }

                    if ($content instanceof Post) {
                        return [
                            'type' => 'post',
                            'id' => $content->id,
                            'content' => $content->content,
                            'image' => $content->image,
                            'video' => $content->video,
                            'author' => $this->authorData(
                                $content->user
                            ),
                        ];
                    }

                    if ($content instanceof Comment) {
                        return [
                            'type' => 'comment',
                            'id' => $content->id,
                            'content' => $content->content,
                            'author' => $this->authorData(
                                $content->user
                            ),
                            'post' => $content->post ? [
                                'id' => $content->post->id,
                                'content' => $content->post->content,
                            ] : null,
                        ];
                    }

                    return null;
                }
            ),
        ];
    }

    private function authorData($user): ?array
    {
        if (!$user) {
            return null;
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'avatar' => $user->avatar,
        ];
    }
}
