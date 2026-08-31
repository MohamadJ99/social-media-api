<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Like;
use App\Models\Post;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    public function storePostLike(Request $request, Post $post)
    {
        return $this->storeLike($request, $post);
    }

    public function destroyPostLike(Request $request, Post $post)
    {
        return $this->destroyLike($request, $post);
    }

    public function storeCommentLike(Request $request, Comment $comment)
    {
        return $this->storeLike($request, $comment);
    }

    public function destroyCommentLike(Request $request, Comment $comment)
    {
        return $this->destroyLike($request, $comment);
    }

    private function storeLike(Request $request, Post|Comment $likeable)
    {
        Like::firstOrCreate([
            'user_id' => $request->user()->id,
            'likeable_id' => $likeable->id,
            'likeable_type' => $likeable::class,
        ]);

        return response()->json([
            'message' => 'Liked successfully',
            'likes_count' => $likeable->likes()->count(),
        ]);
    }

    private function destroyLike(Request $request, Post|Comment $likeable)
    {
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
