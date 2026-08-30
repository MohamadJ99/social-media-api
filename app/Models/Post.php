<?php

namespace App\Models;

use App\Models\Comment as ModelsComment;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Like;
use App\Models\Comment;

#[Fillable(['user_id','content','image'])]
class Post extends Model
{
    public function user():BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function likes():HasMany{
        return $this->hasMany(Like::class);
    }

    public function comments():HasMany{
        return $this->hasMany(Comment::class);
    }
}
