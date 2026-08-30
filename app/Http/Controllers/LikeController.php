<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Like;
class LikeController extends Controller
{
     public function store(Request $request, Post $post)
    {
        $user = $request->user();

        $like = Like::firstOrCreate([
            'user_id' => $user->id,
            'post_id' => $post->id,
        ]);

        return response()->json([
            'message' => 'Post liked successfully',
            'likes_count' => $post->likes()->count(),
        ]);
    }

    public function destroy(Request $request, Post $post)
    {
        $user = $request->user();

        Like::where('user_id', $user->id)
            ->where('post_id', $post->id)
            ->delete();

        return response()->json([
            'message' => 'Post unliked successfully',
            'likes_count' => $post->likes()->count(),
        ]);
    }
}
