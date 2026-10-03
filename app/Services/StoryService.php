<?php

namespace App\Services;

use App\Models\Story;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Friendship;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use App\Jobs\DeleteExpiredStory;
use Throwable;

class StoryService
{
    public function __construct(
        private StoryMediaService $mediaService
    ) {}

    public function create(
        User $user,
        UploadedFile $media,
        ?string $caption,
        string $visibility
    ): Story {
        $mediaData = $this->mediaService->store(
            $media
        );

        try {
            $story = DB::transaction(
                function () use (
                    $user,
                    $mediaData,
                    $caption,
                    $visibility
                ) {
                    return $user->stories()->create([
                        ...$mediaData,

                        'caption' => $caption,

                        'visibility' => $visibility,

                        'expires_at' => now()
                            ->addHours(24),
                    ]);
                }
            );

            DeleteExpiredStory::dispatch(
                $story->id
            )->delay(
                $story->expires_at
            );

            return $story;
        } catch (Throwable $exception) {
            $this->mediaService->delete(
                $mediaData['media_disk'],
                $mediaData['media_path']
            );

            throw $exception;
        }
    }

    public function delete(Story $story): void
    {
        $disk = $story->media_disk;
        $path = $story->media_path;

        DB::transaction(function () use ($story) {
            $story->delete();
        });

        try {
            $this->mediaService->delete(
                $disk,
                $path
            );
        } catch (Throwable $exception) {
            Log::error('Failed to delete story media.', [
                'story_id' => $story->id,
                'disk' => $disk,
                'path' => $path,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    public function getFeed(User $user): Collection
    {
        $stories = Story::query()
            ->active()

            ->where(function (Builder $query) use ($user) {
                $query
                    // My own stories
                    ->where('user_id', $user->id)

                    // Public stories
                    ->orWhere(
                        'visibility',
                        Story::VISIBILITY_PUBLIC
                    )

                    // Friends-only stories
                    ->orWhere(function (Builder $query) use ($user) {
                        $query
                            ->where(
                                'visibility',
                                Story::VISIBILITY_FRIENDS
                            )
                            ->whereExists(
                                function ($friendshipQuery) use ($user) {
                                    $friendshipQuery
                                        ->selectRaw('1')
                                        ->from('friendships')
                                        ->where(
                                            'status',
                                            Friendship::STATUS_ACCEPTED
                                        )
                                        ->where(
                                            function ($pairQuery) use ($user) {
                                                $pairQuery
                                                    ->where(
                                                        function ($direction) use ($user) {
                                                            $direction
                                                                ->whereColumn(
                                                                    'friendships.sender_id',
                                                                    'stories.user_id'
                                                                )
                                                                ->where(
                                                                    'friendships.receiver_id',
                                                                    $user->id
                                                                );
                                                        }
                                                    )
                                                    ->orWhere(
                                                        function ($direction) use ($user) {
                                                            $direction
                                                                ->whereColumn(
                                                                    'friendships.receiver_id',
                                                                    'stories.user_id'
                                                                )
                                                                ->where(
                                                                    'friendships.sender_id',
                                                                    $user->id
                                                                );
                                                        }
                                                    );
                                            }
                                        );
                                }
                            );
                    });
            })

            ->with([
                'user:id,name,username,avatar',
            ])

            ->withExists([
                'views as is_viewed' => function (Builder $query) use ($user) {
                    $query->where(
                        'viewer_id',
                        $user->id
                    );
                },
            ])

            // Stories inside each user group
            ->oldest('created_at')
            ->get();

        return $stories
            ->groupBy('user_id')
            ->map(function (Collection $userStories) use ($user) {
                $storyOwner = $userStories->first()->user;

                $isCurrentUser =
                    $storyOwner->id === $user->id;

                $hasUnseenStories =
                    !$isCurrentUser &&
                    $userStories->contains(
                        fn(Story $story) =>
                        !$story->is_viewed
                    );

                return [
                    'user' => $storyOwner,

                    'is_current_user' =>
                    $isCurrentUser,

                    'has_unseen_stories' =>
                    $hasUnseenStories,

                    'stories' =>
                    $userStories->values(),
                ];
            })
            ->sortBy([
                ['is_current_user', 'desc'],
                ['has_unseen_stories', 'desc'],
            ])
            ->values();
    }

    public function recordView(
        User $user,
        Story $story
    ): void {
        // Owner views should not count
        if ($user->id === $story->user_id) {
            return;
        }

        $now = now();

        DB::table('story_views')->insertOrIgnore([
            'story_id' => $story->id,
            'viewer_id' => $user->id,
            'viewed_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function getViewers(Story $story)
    {
        return $story->views()
            ->with([
                'viewer:id,name,username,avatar',
            ])
            ->latest('viewed_at')
            ->get();
    }
}
