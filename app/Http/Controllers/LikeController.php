<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Like;
use App\Models\Post;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    public function storePostLike(
        Request $request,
        Post $post,
        NotificationService $notificationService
    ) {
        $like = $this->storeLike($request, $post);

        if (
            $like->wasRecentlyCreated &&
            $post->user_id !== $request->user()->id
        ) {
            $notificationService->create(
                $post->user,
                'like',
                "{$request->user()->name} liked your post.",
                $post
            );
        }

        return response()->json([
            'message' => 'Liked successfully',
            'likes_count' => $post->likes()->count(),
        ]);
    }

    public function destroyPostLike(
        Request $request,
        Post $post
    ) {
        return $this->destroyLike($request, $post);
    }

    public function storeCommentLike(
        Request $request,
        Comment $comment,
        NotificationService $notificationService
    ) {
        $like = $this->storeLike($request, $comment);

        if (
            $like->wasRecentlyCreated &&
            $comment->user_id !== $request->user()->id
        ) {
            $notificationService->create(
                $comment->user,
                'like',
                "{$request->user()->name} liked your comment.",
                $comment
            );
        }

        return response()->json([
            'message' => 'Liked successfully',
            'likes_count' => $comment->likes()->count(),
        ]);
    }

    public function destroyCommentLike(
        Request $request,
        Comment $comment
    ) {
        return $this->destroyLike($request, $comment);
    }

    private function storeLike(
        Request $request,
        Post|Comment $likeable
    ): Like {
        return Like::firstOrCreate([
            'user_id' => $request->user()->id,
            'likeable_id' => $likeable->id,
            'likeable_type' => $likeable::class,
        ]);
    }

    private function destroyLike(
        Request $request,
        Post|Comment $likeable
    ) {
        Like::where('user_id', $request->user()->id)
            ->where('likeable_id', $likeable->id)
            ->where('likeable_type', $likeable::class)
            ->delete();

        return response()->json([
            'message' => 'Unliked successfully',
            'likes_count' => $likeable->likes()->count(),
        ]);
    }
}
