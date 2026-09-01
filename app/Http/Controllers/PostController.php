<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\PostRequest;
use App\Models\Post;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $posts = Post::with('user')
            ->withCount(['likes', 'comments'])
            ->withExists([
                'likes as is_liked' => fn($query) =>
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
                'likes as is_liked' => fn($query) =>
                $query->where('user_id', $request->user()->id),
            ]);

        return response()->json([
            'message' => 'Post created successfully',
            'post' => $post,
        ], 201);
    }

    public function update(PostRequest $request, Post $post)
    {
        Gate::authorize('update', $post);

        $post->update([
            'content' => $request->input('content'),
        ]);

        return response()->json([
            'message' => 'Post updated successfully',
            'post' => $post->load('user'),
        ]);
    }

    public function destroy(Post $post)
    {
        Gate::authorize('delete', $post);

        if ($post->image) {
            Storage::disk('public')->delete($post->image);
        }

        $post->delete();

        return response()->json([
            'message' => 'Post deleted successfully',
        ]);
    }
}
