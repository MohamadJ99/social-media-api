<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Comment;
use App\Http\Requests\CommentRequest;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
{
    public function index(Post $post)
    {
        $comments = $post->comments()
            ->with('user:id,name,email')
            ->latest()
            ->get();

        return response()->json([
            'comments' => $comments,
        ]);
    }

    public function store(CommentRequest $request, Post $post ) {

        $comment = $post->comments()->create([
            'user_id' => $request->user()->id,
            'content' => $request->validated('content'),
        ]);

        $comment->load('user:id,name,email');

        return response()->json([
            'message' => 'Comment created successfully',
            'comment' => $comment,
        ], 201);
    }

    public function update( CommentRequest $request, Comment $comment) {

        Gate::authorize('update', $comment);

        $comment->update([
            'content' => $request->validated('content'),
        ]);

        $comment->load('user:id,name,email');

        return response()->json([
            'message' => 'Comment updated successfully',
            'comment' => $comment,
        ]);
    }

    public function destroy( Comment $comment) {
        
        Gate::authorize('delete', $comment);

        $comment->delete();

        return response()->json([
            'message' => 'Comment deleted successfully',
        ]);
    }
}
