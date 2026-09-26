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
    $user = $request->user();

    $conversations = $user
        ->conversations()
        ->with([
            'users:id,name,email,avatar',
            'messages' => function ($query) {
                $query
                    ->latest()
                    ->limit(1);
            },
        ])
        ->withCount([
            'messages as unread_count' => function ($query) use ($user) {
                $query
                    ->where('messages.user_id', '!=', $user->id)
                    ->whereRaw(
                        'messages.created_at > COALESCE(
                            (
                                SELECT last_read_at
                                FROM conversation_user
                                WHERE conversation_user.conversation_id = messages.conversation_id
                                AND conversation_user.user_id = ?
                                LIMIT 1
                            ),
                            ?
                        )',
                        [
                            $user->id,
                            '1970-01-01 00:00:00',
                        ]
                    );
            },
        ])
        ->latest('conversations.updated_at')
        ->get();

    return ConversationResource::collection(
        $conversations
    );
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

    public function markAsRead(  Request $request,  Conversation $conversation) {
    $user = $request->user();

    abort_unless(
        $conversation->users()
            ->where('users.id', $user->id)
            ->exists(),
        403
    );

    $conversation->users()->updateExistingPivot(
        $user->id,
        [
            'last_read_at' => now(),
        ]
    );

    return response()->json([
        'message' => 'Conversation marked as read.',
    ]);
}
}
