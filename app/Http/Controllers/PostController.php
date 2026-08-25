<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostRequest;
use App\Models\Post;

class PostController extends Controller
{
    public function index()
    {
        $posts=Post::with('user')->latest()->get();
        
        return response()->json([
            'posts'=>$posts
        ]);
    }


    public function store(PostRequest $request)
    {
    
    $post=Post::create([
        'user_id'=>$request->user()->id,
        'content'=>$request->input('content'),
        'image'=>$request->input('image'),

    ]);

    return response()->json([
        'message'=>'Post created successfully',
        'post'=>$post->load('user')
    ],201);

    }
}
