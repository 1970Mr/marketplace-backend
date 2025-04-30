<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Panel\Messenger\ChatController;
use App\Http\Controllers\Api\V1\Panel\Messenger\MessageController;
use App\Http\Controllers\Api\V1\Panel\Offers\OfferController;
use App\Http\Controllers\Api\V1\Products\ProductController;
use App\Http\Controllers\Api\V1\Panel\Products\ProductController as PanelProductController;
use App\Http\Controllers\Api\V1\Products\SocialMedia\InstagramAccountController;
use App\Http\Controllers\Api\V1\Products\SocialMedia\TiktokAccountController;
use App\Http\Controllers\Api\V1\Products\SocialMedia\YoutubeChannelController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Auth
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('/user', [AuthController::class, 'getUser'])->middleware('auth:sanctum');

    // Products
    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index']);
        Route::get('/{product:uuid}', [ProductController::class, 'show']);
        Route::get('/{product:uuid}/details', [ProductController::class, 'showProductDetails']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/youtube-channel', [YoutubeChannelController::class, 'store']);
            Route::post('/instagram-account', [InstagramAccountController::class, 'store']);
            Route::post('/tiktok-account', [TiktokAccountController::class, 'store']);
        });
    });

    // Panel
    Route::prefix('panel')->middleware('auth:sanctum')->group(function () {
        // Offers
        Route::get('/offers', [OfferController::class, 'index']);
        Route::post('/offers', [OfferController::class, 'store']);
        Route::patch('/offers/{offer:uuid}/status', [OfferController::class, 'changeStatus']);

        // Chats
        Route::get('/chats', [ChatController::class, 'index']);
        Route::get('/chats/{chat:uuid}', [ChatController::class, 'show']);
        Route::post('/chats/get-or-create', [ChatController::class, 'getOrCreate']);
        Route::get('/chats/{chat:uuid}/messages', [MessageController::class, 'index']);

        // Messages
        Route::post('/messages', [MessageController::class, 'store']);
        Route::patch('/messages/{message:uuid}/read', [MessageController::class, 'markAsRead']);

        // Products
        Route::get('/products', [PanelProductController::class, 'index']);
        Route::get('/products/draft', [PanelProductController::class, 'getDraftProducts']);
        Route::delete('/products/{product:uuid}', [PanelProductController::class, 'destroy']);
    });
});
