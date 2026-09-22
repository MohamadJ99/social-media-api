<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Comment;
use App\Http\Requests\CommentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Services\NotificationService;

class CommentController extends Controller
{

    public function index(Request $request, Post $post)
    {
        $userId = $request->user()->id;

        $loadReplies = function ($query) use (&$loadReplies, $userId) {
            $query
                ->with([
                    'user:id,name,email,avatar',
                    'replies' => $loadReplies,
                ])
                ->withCount('likes')
                ->withExists([
                    'likes as is_liked' => fn($query) =>
                    $query->where('user_id', $userId),
                ])
                ->latest();
        };

        $comments = $post->comments()
            ->whereNull('parent_id')
            ->with([
                'user:id,name,email,avatar',
                'replies' => $loadReplies,
            ])
            ->withCount('likes')
            ->withExists([
                'likes as is_liked' => fn($query) =>
                $query->where('user_id', $userId),
            ])
            ->latest()
            ->get();

        return response()->json([
            'comments' => $comments,
        ]);
    }


    public function store(
        CommentRequest $request,
        Post $post,
        NotificationService $notificationService
    ) {
        $validated = $request->validated();

        if (!empty($validated['parent_id'])) {
            $parentComment = Comment::with('user')->findOrFail(
                $validated['parent_id']
            );

            abort_if(
                $parentComment->post_id !== $post->id,
                422,
                'The parent comment does not belong to this post.'
            );
        }

        $comment = $post->comments()->create([
            'user_id' => $request->user()->id,
            'content' => $validated['content'],
            'parent_id' => $validated['parent_id'] ?? null,
        ]);

        if (!empty($validated['parent_id'])) {

            // Reply to a comment
            if ($parentComment->user_id !== $request->user()->id) {
                $notificationService->create(
                    $parentComment->user,
                    'comment',
                    "{$request->user()->name} replied to your comment.",
                    $comment
                );
            }
        } elseif ($post->user_id !== $request->user()->id) {

            // Comment on a post
            $notificationService->create(
                $post->user,
                'comment',
                "{$request->user()->name} commented on your post.",
                $comment
            );
        }

        $comment->load('user:id,name,email');

        return response()->json([
            'message' => 'Comment created successfully',
            'comment' => $comment,
        ], 201);
    }

    public function update(CommentRequest $request, Comment $comment)
    {

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

    public function destroy(Comment $comment)
    {

        Gate::authorize('delete', $comment);

        $comment->delete();

        return response()->json([
            'message' => 'Comment deleted successfully',
        ]);
    }
}
