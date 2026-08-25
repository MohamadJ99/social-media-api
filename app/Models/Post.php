<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
#[Fillable(['user_id','content','image'])]
class Post extends Model
{
    public function user():BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
