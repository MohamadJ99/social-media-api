<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;

class ReportPolicy
{
    public function create(
        User $user,
        Post|Comment $reportable
    ): bool {
        return $reportable->user_id !== $user->id;
    }
}