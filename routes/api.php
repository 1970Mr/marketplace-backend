<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Products\ProductController;
use App\Http\Controllers\Api\V1\Products\SocialMedia\InstagramAccountController;
use App\Http\Controllers\Api\V1\Products\SocialMedia\YoutubeChannelController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('/user', [AuthController::class, 'getUser'])->middleware('auth:sanctum');

    Route::post('/products/youtube-channel', [YoutubeChannelController::class, 'store'])->middleware('auth:sanctum');
    Route::post('/products/instagram-account', [InstagramAccountController::class, 'store'])->middleware('auth:sanctum');
//    Route::post('/tiktok-account', [TiktokAccountController::class, 'store'])->middleware('auth:sanctum');

    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product:uuid}', [ProductController::class, 'show']);
});
