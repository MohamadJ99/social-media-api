<?php

namespace App\Policies;

use App\Models\Friendship;
use App\Models\Story;
use App\Models\User;

class StoryPolicy
{
    public function view(User $user, Story $story): bool
    {
        if ($story->isExpired()) {
            return false;
        }

        if ($user->id === $story->user_id) {
            return true;
        }

        if ($story->visibility === Story::VISIBILITY_PUBLIC) {
            return true;
        }

        if ($story->visibility !== Story::VISIBILITY_FRIENDS) {
            return false;
        }

        return Friendship::query()
            ->where(
                'status',
                Friendship::STATUS_ACCEPTED
            )
            ->where(function ($query) use ($user, $story) {
                $query
                    ->where(function ($query) use ($user, $story) {
                        $query
                            ->where('sender_id', $user->id)
                            ->where('receiver_id', $story->user_id);
                    })
                    ->orWhere(function ($query) use ($user, $story) {
                        $query
                            ->where('sender_id', $story->user_id)
                            ->where('receiver_id', $user->id);
                    });
            })
            ->exists();
    }

    public function delete(User $user, Story $story): bool
    {
        return $user->id === $story->user_id;
    }

    public function viewViewers(User $user, Story $story): bool
    {
        return $user->id === $story->user_id;
    }
}