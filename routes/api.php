<?php

use App\Http\Controllers\Api\V1\Admin\Agents\AgentController;
use App\Http\Controllers\Api\V1\Admin\Auth\AuthController as AdminAuthController;
use App\Http\Controllers\Api\V1\Admin\Escrow\EscrowController as AdminEscrowController;
use App\Http\Controllers\Api\V1\Admin\ProductManagement\ProductManagementController;
use App\Http\Controllers\Api\V1\Admin\UserManagement\UserManagementController;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Panel\Escrow\EscrowController as PanelEscrowController;
use App\Http\Controllers\Api\V1\Panel\Messenger\ChatController;
use App\Http\Controllers\Api\V1\Panel\Messenger\MessageController;
use App\Http\Controllers\Api\V1\Panel\Offers\OfferController;
use App\Http\Controllers\Api\V1\Panel\Products\ProductController as PanelProductController;
use App\Http\Controllers\Api\V1\Panel\WatchList\WatchListController;
use App\Http\Controllers\Api\V1\Products\ProductController;
use App\Http\Controllers\Api\V1\Products\SocialMedia\InstagramAccountController;
use App\Http\Controllers\Api\V1\Products\SocialMedia\TiktokAccountController;
use App\Http\Controllers\Api\V1\Products\SocialMedia\YoutubeChannelController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Auth
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('/me', [AuthController::class, 'me'])->middleware('auth:sanctum');

    // Products
    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index']);
        Route::get('/{product:uuid}', [ProductController::class, 'show']);
        Route::get('/{product:uuid}/details', [ProductController::class, 'showProductDetails']);

        // Special products
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/youtube-channel', [YoutubeChannelController::class, 'store']);
            Route::post('/instagram-account', [InstagramAccountController::class, 'store']);
            Route::post('/tiktok-account', [TiktokAccountController::class, 'store']);
            Route::post('/tiktok-account/{product:uuid}/verify', [TiktokAccountController::class, 'verify']);
        });
    });

    // Panel
    Route::prefix('panel')->middleware('auth:sanctum')->group(function () {
        // Offers
        Route::prefix('offers')->group(function () {
            Route::get('/seller', [OfferController::class, 'sellerOffers']);
            Route::get('/seller/accepted', [OfferController::class, 'acceptedSellerOffers']);
            Route::get('/buyer', [OfferController::class, 'buyerOffers']);
            Route::get('/buyer/accepted', [OfferController::class, 'acceptedBuyerOffers']);
            Route::post('/', [OfferController::class, 'store']);
            Route::delete('/{offer:uuid}', [OfferController::class, 'destroy']);
            Route::patch('/{offer:uuid}/status', [OfferController::class, 'changeStatus']);
        });

        // Chats
        Route::prefix('chats')->group(function () {
            Route::get('/', [ChatController::class, 'index']);
            Route::get('/{chat:uuid}', [ChatController::class, 'show']);
            Route::post('/get-or-create', [ChatController::class, 'getOrCreate']);

            // Chat Messages
            Route::prefix('/{chat:uuid}/messages')->group(function () {
                Route::get('/', [MessageController::class, 'index']);
            });
        });

        // Messages
        Route::prefix('messages')->group(function () {
            Route::post('/', [MessageController::class, 'store']);
            Route::patch('/{message:uuid}/read', [MessageController::class, 'markAsRead']);
        });

        // Products
        Route::prefix('products')->group(function () {
            Route::get('/', [PanelProductController::class, 'index']);
            Route::get('/draft', [PanelProductController::class, 'getDraftProducts']);
            Route::delete('/{product:uuid}', [PanelProductController::class, 'destroy']);
        });

        // Watch List
        Route::prefix('watchlist')->group(function () {
            Route::get('/', [WatchListController::class, 'index']);
            Route::post('/{product:uuid}/toggle', [WatchListController::class, 'toggle']);
        });

        // Escrow (User)
        Route::prefix('escrows')->group(function () {
            Route::get('/', [PanelEscrowController::class, 'getUserEscrows']);
            Route::get('{escrow:uuid}', [PanelEscrowController::class, 'show']);
            Route::post('/', [PanelEscrowController::class, 'store']);
            Route::post('{escrow:uuid}/signatures/buyer', [PanelEscrowController::class, 'uploadBuyerSignature']);
            Route::post('{escrow:uuid}/signatures/seller', [PanelEscrowController::class, 'uploadSellerSignature']);
            Route::post('{escrow:uuid}/receipts', [PanelEscrowController::class, 'uploadReceipts']);
            Route::post('{escrow:uuid}/slots/propose', [PanelEscrowController::class, 'proposeSlots']);
            Route::post('{escrow:uuid}/slots/select', [PanelEscrowController::class, 'selectSlot']);
            Route::post('{escrow:uuid}/slots/reject', [PanelEscrowController::class, 'rejectScheduling']);
        });
    });

    // Admin Auth
    Route::prefix('admin')->group(function () {
        Route::post('/login', [AdminAuthController::class, 'login']);
        Route::post('/logout', [AdminAuthController::class, 'logout'])->middleware(['auth:admin-api']);
        Route::get('/me', [AdminAuthController::class, 'me'])->middleware('auth:admin-api');
    });

    // Admin
    Route::prefix('admin')->middleware(['auth:admin-api'])->group(function () {
        // Agents
        Route::prefix('agents')->group(function () {
            Route::get('/', [AgentController::class, 'index']);
            Route::get('/{admin}', [AgentController::class, 'show']);
            Route::post('/', [AgentController::class, 'store']);
            Route::put('/{admin}', [AgentController::class, 'update']);
            Route::put('/{admin}/permissions', [AgentController::class, 'updatePermissions']);
            Route::patch('/{admin}/status', [AgentController::class, 'toggleStatus']);
        });

        // User Management
        Route::prefix('user-management')->group(function () {
            Route::get('/', [UserManagementController::class, 'index']);
            Route::get('/{user}', [UserManagementController::class, 'show']);
            Route::patch('/{user}/note', [UserManagementController::class, 'updateNote']);
            Route::patch('/{user}/status', [UserManagementController::class, 'updateStatus']);
            Route::get('/{user}/chats', [UserManagementController::class, 'userChats']);
        });

        // Product Management
        Route::prefix('product-management')->group(function () {
            Route::get('/', [ProductManagementController::class, 'index']);
            Route::get('/{product_id}', [ProductManagementController::class, 'show']);
            Route::patch('/{product_id}/status', [ProductManagementController::class, 'updateStatus']);
            Route::delete('/{product}', [ProductManagementController::class, 'destroy']);
        });

        // Escrow (Admin)
        Route::prefix('escrows')->group(function () {
            Route::get('/', [AdminEscrowController::class, 'getAdminEscrows']);
            Route::post('{escrow:uuid}/accept', [AdminEscrowController::class, 'accept']);
            Route::post('{escrow:uuid}/payment/confirm', [AdminEscrowController::class, 'confirmPayment']);
            Route::post('{escrow:uuid}/delivery/confirm', [AdminEscrowController::class, 'confirmDelivery']);
            Route::post('{escrow:uuid}/payout/release', [AdminEscrowController::class, 'releaseFunds']);
            Route::post('{escrow:uuid}/cancel', [AdminEscrowController::class, 'cancel']);
            Route::post('{escrow:uuid}/refund', [AdminEscrowController::class, 'refund']);
        });

//        // Escrow (User)
//        Route::prefix('escrows')->group(function () {
//            Route::get('/', [PanelEscrowController::class, 'getUserEscrows']);
//            Route::get('{escrow:uuid}', [PanelEscrowController::class, 'show']);
//            Route::post('/', [PanelEscrowController::class, 'store']);
//            Route::post('{escrow:uuid}/signatures/buyer', [PanelEscrowController::class, 'uploadBuyerSignature']);
//            Route::post('{escrow:uuid}/signatures/seller', [PanelEscrowController::class, 'uploadSellerSignature']);
//            Route::post('{escrow:uuid}/receipts', [PanelEscrowController::class, 'uploadReceipts']);
//            Route::post('{escrow:uuid}/slots/propose', [PanelEscrowController::class, 'proposeSlots']);
//            Route::post('{escrow:uuid}/slots/select', [PanelEscrowController::class, 'selectSlot']);
//            Route::post('{escrow:uuid}/slots/reject', [PanelEscrowController::class, 'rejectScheduling']);
//        });
    });
});
