<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchUsersRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use App\Http\Requests\UpdateAvatarRequest;
use App\Http\Requests\UpdateCoverImageRequest;
use App\Http\Resources\SearchUserResource;
use App\Services\ProfileService;
use App\Services\FriendshipService;
use App\Http\Requests\UpdatePasswordRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function show($id, FriendshipService $friendshipService)
    {
        $user = User::query()
            ->withCount('posts')
            ->findOrFail($id);

        $user->friends_count = $friendshipService->getFriendsCount($user);

        $friendship = $friendshipService->getFriendship(
            request()->user(),
            $user
        );
        $user->friendship = $friendship;

        return new UserResource($user);
    }

    public function me(Request $request, FriendshipService $friendshipService)
    {
        $user = $request->user();

        $user->friends_count = $friendshipService->getFriendsCount($user);

        return new UserResource($user);
    }

    public function search(SearchUsersRequest $request)
    {
        $search = trim(
            $request->string('q')->toString()
        );

        $users = User::query()
            ->where('id', '!=', $request->user()->id)
            ->where(function ($query) use ($search) {
                $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate(10);

        return SearchUserResource::collection($users);
    }

    public function update(UpdateProfileRequest $request)
    {
        $user = $request->user();
        $user->update($request->validated());
        return new UserResource($user->fresh());
    }

    public function updateAvatar(UpdateAvatarRequest $request, ProfileService $profileService)
    {
        $user = $profileService->updateAvatar(
            $request->user(),
            $request->file('avatar')
        );

        return new UserResource($user);
    }

    public function updateCoverImage(UpdateCoverImageRequest $request, ProfileService $profileService)
    {
        $user = $profileService->updateCoverImage(
            $request->user(),
            $request->file('cover_image')
        );

        return new UserResource($user);
    }



    public function updatePassword(UpdatePasswordRequest $request)
    {
        $user = $request->user();

        if (!Hash::check(
            $request->string('current_password')->toString(),
            $user->password
        )) {
            throw ValidationException::withMessages([
                'current_password' => [
                    'The current password is incorrect.',
                ],
            ]);
        }

        $user->update([
            'password' => Hash::make(
                $request->string('password')->toString()
            ),
        ]);

        return response()->json([
            'message' => 'Password updated successfully.',
        ]);
    }
}
