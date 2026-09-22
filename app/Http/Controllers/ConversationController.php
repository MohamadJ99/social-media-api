<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateConversationRequest;
use App\Models\Conversation;
use Illuminate\Http\Request;
use App\Http\Resources\ConversationResource;

class ConversationController extends Controller
{
    public function index(Request $request)
    {
        $conversations = $request->user()
            ->conversations()
            ->with([
                'users:id,name,email,avatar',
                'messages' => function ($query) {
                    $query
                        ->latest()
                        ->limit(1);
                },
            ])
            ->latest('conversations.updated_at')
            ->get();

        return ConversationResource::collection($conversations);
    }

    public function store(CreateConversationRequest $request)
    {
        $currentUser = $request->user();

        $otherUserId = $request->validated('user_id');

        abort_if(
            $currentUser->id === $otherUserId,
            422,
            'You cannot start a conversation with yourself.'
        );

        $conversation = Conversation::query()
            ->whereHas('users', function ($query) use ($currentUser) {
                $query->where('users.id', $currentUser->id);
            })
            ->whereHas('users', function ($query) use ($otherUserId) {
                $query->where('users.id', $otherUserId);
            })
            ->withCount('users')
            ->first();

        if (!$conversation) {
            $conversation = Conversation::create();

            $conversation->users()->attach([
                $currentUser->id,
                $otherUserId,
            ]);
        }

        $conversation->load([
            'users:id,name,email,avatar',
            'messages' => function ($query) {
                $query
                    ->latest()
                    ->limit(1);
            },
        ]);

        return new ConversationResource($conversation);
    }
}
