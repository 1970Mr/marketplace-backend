<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Products\SocialMedia\YoutubeChannelController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('/user', [AuthController::class, 'getUser'])->middleware('auth:sanctum');

    Route::post('/youtube-channel', [YoutubeChannelController::class, 'store'])->middleware('auth:sanctum');
//    Route::post('/tiktok-channel/step-1', [TiktokChannelController::class, 'step1']);
//    Route::post('/instagram-channel/step-1', [InstagramChannelController::class, 'step1']);
});
