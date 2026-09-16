<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FriendshipController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

// Public Routes
Route::middleware('throttle:auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});


// Protected Routes
Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Users
    Route::get('/me', [UserController::class, 'me']);
    Route::patch('/me',[UserController::class,'update']);
    Route::post('/me/avatar', [UserController::class, 'updateAvatar']);
    Route::post('/me/cover', [UserController::class, 'updateCoverImage']);
    Route::get('/users/{id}', [UserController::class, 'show']);

    // Posts
    Route::get('/posts', [PostController::class, 'index']);
    Route::get('/posts/{post}', [PostController::class, 'show']);
    Route::post('/posts', [PostController::class, 'store']);
    Route::patch('/posts/{post}', [PostController::class, 'update']);
    Route::delete('/posts/{post}', [PostController::class, 'destroy']);

    // Post Likes
    Route::post('/posts/{post}/like', [LikeController::class, 'storePostLike']);
    Route::delete('/posts/{post}/like', [LikeController::class, 'destroyPostLike']);

    // Comment & Reply Likes
    Route::post('/comments/{comment}/like', [LikeController::class, 'storeCommentLike']);
    Route::delete('/comments/{comment}/like', [LikeController::class, 'destroyCommentLike']);

    // Comments
    Route::get('/posts/{post}/comments', [CommentController::class, 'index']);
    Route::post('/posts/{post}/comments', [CommentController::class, 'store']);
    Route::patch('/comments/{comment}', [CommentController::class, 'update']);
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);

    // Friendships
    Route::get('/friends',[FriendshipController::class, 'getFriends']);
    Route::get('/friend-requests/incoming',[FriendshipController::class, 'getIncomingRequests']);
    Route::get('/friend-requests/outgoing',[FriendshipController::class, 'getOutgoingRequests']);
    Route::post('/users/{user}/friend',[FriendshipController::class,'sendRequest']);
    Route::post('/friendships/{friendship}/accept',[FriendshipController::class, 'acceptRequest']);
    Route::post('/friendships/{friendship}/reject',[FriendshipController::class, 'rejectRequest']);
    Route::delete('/friendships/{friendship}/cancel',[FriendshipController::class, 'cancelRequest']);
    Route::delete('/friendships/{friendship}',[FriendshipController::class, 'removeFriend']);
    
   // Notifications

   Route::get('/notifications', [NotificationController::class, 'index']);
   Route::patch('/notifications/{notification}/read',[NotificationController::class, 'markAsRead']);
   Route::get('/notifications/unread-count',[NotificationController::class, 'unreadCount']);
});
