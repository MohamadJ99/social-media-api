<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
         $currentUserId = $request->user()->id;

        $otherUser = $this->users
            ->firstWhere('id', '!=', $currentUserId);

        $lastMessage = $this->messages->first();

        return [
            'id' => $this->id,

            'user' => $otherUser ? [
                'id' => $otherUser->id,
                'name' => $otherUser->name,
                'email' => $otherUser->email,
                'avatar' => $otherUser->avatar,
            ] : null,

            'last_message' => $lastMessage ? [
                'id' => $lastMessage->id,
                'body' => $lastMessage->body,
                'user_id' => $lastMessage->user_id,
                'created_at' => $lastMessage->created_at,
            ] : null,

            'updated_at' => $this->updated_at,
        ];
    
    }
}
