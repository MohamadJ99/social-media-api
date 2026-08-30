<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use App\Models\Post;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['user_id','post_id','content'])]
class Comment extends Model
{
    
public function user():BelongsTo
{
 return $this->belongsTo(User::class);
}

public function post():BelongsTo
{
 return $this->belongsTo(Post::class);
}

}
