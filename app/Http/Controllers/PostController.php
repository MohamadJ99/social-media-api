<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\PostRequest;
use App\Models\Post;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $posts = Post::with('user')
            ->withCount(['likes', 'comments'])
            ->withExists([
                'likes as is_liked' => fn ($query) =>
                    $query->where('user_id', $request->user()->id),
            ])
            ->latest()
            ->get();

        return response()->json([
            'posts' => $posts,
        ]);
    }

    public function store(PostRequest $request)
    {
        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request
                ->file('image')
                ->store('posts', 'public');
        }

        $post = Post::create([
            'user_id' => $request->user()->id,
            'content' => $request->input('content'),
            'image' => $imagePath,
        ]);

        $post
            ->load('user')
            ->loadCount(['likes', 'comments'])
            ->loadExists([
                'likes as is_liked' => fn ($query) =>
                    $query->where('user_id', $request->user()->id),
            ]);

        return response()->json([
            'message' => 'Post created successfully',
            'post' => $post,
        ], 201);
    }

    public function destroy(Post $post)
    {
        if ($post->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'You are not authorized to delete this post',
            ], 403);
        }

        $post->delete();

        return response()->json([
            'message' => 'Post deleted successfully',
        ]);
    }
}

