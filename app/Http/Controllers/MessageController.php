<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMessageRequest;
use App\Http\Requests\UpdateMessageRequest;
use App\Http\Resources\MessageResource;
use App\Models\Conversation;
use Illuminate\Http\Request;
use App\Models\Message;
use App\Events\MessageSent;
use App\Events\MessageReceived;

class MessageController extends Controller
{
    public function index(Request $request,   Conversation $conversation)
    {

        abort_unless(
            $conversation->users()
                ->where('users.id', $request->user()->id)
                ->exists(),
            403
        );

        $messages = $conversation
            ->messages()
            ->with('user:id,name,email,avatar')
            ->latest()
            ->paginate(30);

        return MessageResource::collection($messages);
    }

    public function store(
        StoreMessageRequest $request,
        Conversation $conversation
    ) {
        $currentUser = $request->user();

        abort_unless(
            $conversation->users()
                ->where('users.id', $currentUser->id)
                ->exists(),
            403
        );

        $recipientId = $conversation
            ->users()
            ->where('users.id', '!=', $currentUser->id)
            ->value('users.id');

        abort_unless($recipientId, 422);

        $message = $conversation->messages()->create([
            'user_id' => $currentUser->id,
            'body' => $request->validated('body'),
        ]);

        $message->load('user:id,name,email,avatar');

        $conversation->touch();

        MessageSent::dispatch($message);

        MessageReceived::dispatch(
            $message,
            $recipientId
        );

        return new MessageResource($message);
    }

    public function destroy(Request $request, Message $message)
    {

        abort_unless(
            $message->user_id === $request->user()->id,
            403
        );

        $message->delete();

        return response()->json([
            'message' => 'Message deleted successfully',
        ]);
    }


    public function update(UpdateMessageRequest $request, Message $message)
    {

        abort_unless(
            $message->user_id === $request->user()->id,
            403
        );

        $message->update([
            'body' => $request->validated('body'),
        ]);

        $message->load('user:id,name,email,avatar');

        return new MessageResource($message);
    }
}
