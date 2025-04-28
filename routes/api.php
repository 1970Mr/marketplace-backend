<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Messenger\ChatController;
use App\Http\Controllers\Api\V1\Messenger\MessageController;
use App\Http\Controllers\Api\V1\Offers\OfferController;
use App\Http\Controllers\Api\V1\Products\ProductController;
use App\Http\Controllers\Api\V1\Products\SocialMedia\InstagramAccountController;
use App\Http\Controllers\Api\V1\Products\SocialMedia\TiktokAccountController;
use App\Http\Controllers\Api\V1\Products\SocialMedia\YoutubeChannelController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('/user', [AuthController::class, 'getUser'])->middleware('auth:sanctum');

    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product:uuid}', [ProductController::class, 'show']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/products/youtube-channel', [YoutubeChannelController::class, 'store'])->middleware('auth:sanctum');
        Route::post('/products/instagram-account', [InstagramAccountController::class, 'store'])->middleware('auth:sanctum');
        Route::post('/products/tiktok-account', [TiktokAccountController::class, 'store'])->middleware('auth:sanctum');
    });

    Route::prefix('panel')->middleware('auth:sanctum')->group(function () {
        // Offers
        Route::get('/offers', [OfferController::class, 'index']);
        Route::post('/offers', [OfferController::class, 'store']);
        Route::patch('/offers/{offer:uuid}/status', [OfferController::class, 'changeStatus']);

        // Chats
        Route::get('/chats', [ChatController::class, 'index']);
        Route::get('/chats/{chat}', [ChatController::class, 'show']);

        // Messages
        Route::get('/chats/{chat:uuid}/messages', [MessageController::class, 'index']);
        Route::post('/messages', [MessageController::class, 'store']);
        Route::patch('/messages/{message:uuid}/read', [MessageController::class, 'markAsRead']);
    });
});
