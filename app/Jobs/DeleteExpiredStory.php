<?php

namespace App\Jobs;

use App\Models\Story;
use App\Services\StoryMediaService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class DeleteExpiredStory implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        public int $storyId
    ) {
    }

    public function handle(
        StoryMediaService $mediaService
    ): void {
        $story = Story::find($this->storyId);

        // Story may have already been deleted manually.
        if (!$story) {
            return;
        }

        // Extra safety.
        if (!$story->isExpired()) {
            return;
        }

        $disk = $story->media_disk;
        $path = $story->media_path;

        /*
         * Delete DB record first.
         * story_views will be deleted by cascade.
         */
        $story->delete();

        try {
            $mediaService->delete(
                $disk,
                $path
            );
        } catch (Throwable $exception) {
            Log::error(
                'Failed to delete expired story media.',
                [
                    'story_id' => $this->storyId,
                    'disk' => $disk,
                    'path' => $path,
                    'error' => $exception->getMessage(),
                ]
            );

            throw $exception;
        }
    }
}