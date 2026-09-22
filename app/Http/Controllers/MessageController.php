<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMessageRequest;
use App\Http\Requests\UpdateMessageRequest;
use App\Http\Resources\MessageResource;
use App\Models\Conversation;
use Illuminate\Http\Request;
use App\Models\Message;

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

    public function store(StoreMessageRequest $request,  Conversation $conversation)
    {

        abort_unless(
            $conversation->users()
                ->where('users.id', $request->user()->id)
                ->exists(),
            403
        );

        $message = $conversation->messages()->create([
            'user_id' => $request->user()->id,
            'body' => $request->validated('body'),
        ]);

        $message->load('user:id,name,email,avatar');

        $conversation->touch();

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


    public function update( UpdateMessageRequest $request, Message $message ) {

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
