<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use App\Http\Requests\UpdateAvatarRequest;
use App\Http\Requests\UpdateCoverImageRequest;
use App\Services\ProfileService;

class UserController extends Controller
{
    public function show($id)
    {
        $user = User::findOrFail($id);

        return new UserResource($user);
    }

    public function me(Request $request)
    {
        return new UserResource($request->user());
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
}
