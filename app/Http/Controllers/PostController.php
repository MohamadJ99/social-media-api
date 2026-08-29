<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostRequest;
use App\Models\Post;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::with('user')->latest()->get();

        return response()->json([
            'posts' => $posts
        ]);
    }

    public function store(PostRequest $request)
    {
        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('posts', 'public');
        }

        $post = Post::create([
            'user_id' => $request->user()->id,
            'content' => $request->input('content'),
            'image' => $imagePath,
        ]);

        return response()->json([
            'message' => 'Post created successfully',
            'post' => $post->load('user'),
        ], 201);
    }


    public function destroy(Post $post)
    {

        if ($post->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'You are not authorized to delete this post'
            ], 403);
        }

        $post->delete();

        return response()->json([
            'message' => 'Post deleted successfully'
        ]);
    }
}
