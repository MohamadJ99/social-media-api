<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProfileService
{
    public function updateAvatar(User $user, UploadedFile $avatar): User
    {
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = $avatar->store('avatars', 'public');

        $user->update([
            'avatar' => $path,
        ]);

        return $user->fresh();
    }

    public function updateCoverImage(User $user, UploadedFile $coverImage): User
    {
        if ($user->cover_image) {
            Storage::disk('public')->delete($user->cover_image);
        }

        $path = $coverImage->store('covers', 'public');

        $user->update([
            'cover_image' => $path,
        ]);

        return $user->fresh();
    }
}
