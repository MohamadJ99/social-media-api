<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Friendship;
use App\Services\FriendshipService;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use App\Http\Resources\FriendRequestResource;

class FriendshipController extends Controller
{

    public function getFriends(
        Request $request,
        FriendshipService $friendshipService
    ) {
        $friends = $friendshipService->getFriends(
            $request->user()
        );

        return UserResource::collection($friends);
    }
    public function sendRequest(Request $request, User $user, FriendshipService $friendshipService)
    {
        $friendship = $friendshipService->sendRequest(
            $request->user(),
            $user
        );

        return response()->json([
            'message' => 'Friend request sent successfully.',
            'friendship' => $friendship,
        ], 201);
    }

    public function acceptRequest(Request $request, Friendship $friendship, FriendshipService $friendshipService)
    {
        $friendship = $friendshipService->acceptRequest(
            $request->user(),
            $friendship
        );

        return response()->json([
            'message' => 'Friend request accepted successfully.',
            'friendship' => $friendship,
        ]);
    }

    public function rejectRequest(Request $request, Friendship $friendship, FriendshipService $friendshipService)
    {
        $friendshipService->rejectRequest(
            $request->user(),
            $friendship
        );

        return response()->json([
            'message' => 'Friend request rejected successfully.',
        ]);
    }

    public function cancelRequest(Request $request, Friendship $friendship, FriendshipService $friendshipService)
    {
        $friendshipService->cancelRequest(
            $request->user(),
            $friendship
        );

        return response()->json([
            'message' => 'Friend request cancelled successfully.',
        ]);
    }

    public function removeFriend(Request $request, Friendship $friendship, FriendshipService $friendshipService)
    {
        $friendshipService->removeFriend(
            $request->user(),
            $friendship
        );

        return response()->json([
            'message' => 'Friend removed successfully.',
        ]);
    }

    public function getIncomingRequests(Request $request, FriendshipService $friendshipService)

    {
        $requests = $friendshipService->getIncomingRequests(
            $request->user()
        );

        return FriendRequestResource::collection(
            $requests->map(function ($request) {
                $request->setRelation('user', $request->sender);

                return $request;
            })
        );
    }

    public function getOutgoingRequests(Request $request, FriendshipService $friendshipService)
    {
        $requests = $friendshipService->getOutgoingRequests(
            $request->user()
        );

        return FriendRequestResource::collection(
            $requests->map(function ($request) {
                $request->setRelation('user', $request->receiver);

                return $request;
            })
        );
    }
}
