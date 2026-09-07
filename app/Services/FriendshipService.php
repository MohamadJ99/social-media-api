<?php

namespace App\Services;

use App\Models\Friendship;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class FriendshipService
{

    public function getFriends(User $user)
    {
        return User::query()
            ->whereHas('sentFriendRequests', function ($query) use ($user) {
                $query
                    ->where('receiver_id', $user->id)
                    ->where('status', 'accepted');
            })
            ->orWhereHas('receivedFriendRequests', function ($query) use ($user) {
                $query
                    ->where('sender_id', $user->id)
                    ->where('status', 'accepted');
            })
            ->get();
    }

    public function sendRequest(User $sender, User $receiver): Friendship
    {
        if ($sender->is($receiver)) {
            throw ValidationException::withMessages([
                'user' => 'You cannot send a friend request to yourself.',
            ]);
        }

        $existingFriendship = Friendship::query()
            ->where(function ($query) use ($sender, $receiver) {
                $query->where('sender_id', $sender->id)
                    ->where('receiver_id', $receiver->id);
            })
            ->orWhere(function ($query) use ($sender, $receiver) {
                $query->where('sender_id', $receiver->id)
                    ->where('receiver_id', $sender->id);
            })
            ->first();

        if ($existingFriendship) {
            throw ValidationException::withMessages([
                'user' => 'A friendship or friend request already exists.',
            ]);
        }

        return Friendship::create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'status' => 'pending',
        ]);
    }

    public function acceptRequest(User $user, Friendship $friendship): Friendship
    {
        if ($friendship->receiver_id !== $user->id) {
            throw ValidationException::withMessages([
                'friendship' => 'You are not allowed to accept this friend request.',
            ]);
        }

        if ($friendship->status !== 'pending') {
            throw ValidationException::withMessages([
                'friendship' => 'This friend request cannot be accepted.',
            ]);
        }

        $friendship->update([
            'status' => 'accepted',
        ]);

        return $friendship->refresh();
    }


    public function rejectRequest(
        User $user,
        Friendship $friendship
    ): void {
        if ($friendship->receiver_id !== $user->id) {
            throw ValidationException::withMessages([
                'friendship' => 'You are not allowed to reject this friend request.',
            ]);
        }

        if ($friendship->status !== 'pending') {
            throw ValidationException::withMessages([
                'friendship' => 'This friend request cannot be rejected.',
            ]);
        }

        $friendship->delete();
    }

    public function cancelRequest(
        User $user,
        Friendship $friendship
    ): void {
        if ($friendship->sender_id !== $user->id) {
            throw ValidationException::withMessages([
                'friendship' => 'You are not allowed to cancel this friend request.',
            ]);
        }

        if ($friendship->status !== 'pending') {
            throw ValidationException::withMessages([
                'friendship' => 'This friend request cannot be cancelled.',
            ]);
        }

        $friendship->delete();
    }

    public function removeFriend(
        User $user,
        Friendship $friendship
    ): void {
        $isParticipant =
            $friendship->sender_id === $user->id
            || $friendship->receiver_id === $user->id;

        if (! $isParticipant) {
            throw ValidationException::withMessages([
                'friendship' => 'You are not part of this friendship.',
            ]);
        }

        if ($friendship->status !== 'accepted') {
            throw ValidationException::withMessages([
                'friendship' => 'This friendship cannot be removed.',
            ]);
        }

        $friendship->delete();
    }

    public function getIncomingRequests(User $user)
    {
        return Friendship::query()
            ->with('sender')
            ->where('receiver_id', $user->id)
            ->where('status', 'pending')
            ->latest()
            ->get();
    }

    public function getOutgoingRequests(User $user)
    {
        return Friendship::query()
            ->with('receiver')
            ->where('sender_id', $user->id)
            ->where('status', 'pending')
            ->latest()
            ->get();
    }
}
