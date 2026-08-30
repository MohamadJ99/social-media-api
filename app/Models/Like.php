<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use App\Models\Post;

#[Fillable('user_id','post_id')]
class Like extends Model
{
    public function user() :BelongsTo{

     return $this->belongsTo(User::class);
    }


    public function post():BelongsTo{
        return $this->belongsTo(Post::class);
    }
}
