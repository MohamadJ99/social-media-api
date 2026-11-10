<?php

namespace App\Http\Resources\Admin;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
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
                fn () => $this->authorData($this->user)
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

            'reviews' => $this->whenLoaded(
                'reviews',
                fn () => $this->reviews->map(
                    fn ($review) => [
                        'id' => $review->id,

                        'previous_status' =>
                            $review->previous_status->value,

                        'new_status' =>
                            $review->new_status->value,

                        'note' => $review->note,

                        'reviewer' => $this->authorData(
                            $review->reviewer
                        ),

                        'created_at' =>
                            $review->created_at?->toISOString(),
                    ]
                )
            ),
        ];
    }

    private function authorData(?User $user): ?array
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
