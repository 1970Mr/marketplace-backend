<?php

namespace App\Services\Panel\Messenger;

use App\Models\Chat;
use App\Models\Products\Product;
use Illuminate\Database\Eloquent\Collection;

class ChatService
{
    public function getUserChats(int $userId): Collection
    {
        return Chat::with(['buyer', 'seller', 'product.productable', 'lastMessage'])
            ->where(function ($query) use ($userId) {
                $query->where('buyer_id', $userId)
                    ->orWhere('seller_id', $userId);
            })
            ->whereNull('escrow_id')
            ->withCount('unreadMessages')
            ->get()
            ->sortByDesc(fn($chat) => optional($chat->lastMessage)->created_at)
            ->values();
    }

    public function findOrCreateChat(string $productUuid, int $buyerId): Chat {
        $product = Product::where('uuid', $productUuid)->firstOrFail();

        return Chat::firstOrCreate([
            'product_id' => $product->id,
            'buyer_id' => $buyerId,
            'seller_id' => $product->user_id
        ]);
    }

    public function loadChatRelations(Chat $chat): Chat
    {
        return $chat->load([
            'product.productable',
            'buyer',
            'seller',
            'messages',
            'lastMessage',
        ])->loadCount('unreadMessages');
    }
}
