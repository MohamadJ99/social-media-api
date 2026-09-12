<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class NotificationService
{
    public function create(
        User $user,
        string $type,
        string $message,
        ?Model $notifiable = null
    ): Notification {
        return $user->notifications()->create([
            'type' => $type,
            'message' => $message,
            'notifiable_type' => $notifiable?->getMorphClass(),
            'notifiable_id' => $notifiable?->getKey(),
        ]);
    }
}