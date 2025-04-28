<?php

namespace App\Http\Controllers\Api\V1\Messenger;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Messenger\ChatRequest;
use App\Http\Resources\V1\Messenger\ChatResource;
use App\Models\Chat;
use App\Models\Products\Product;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ChatController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $chats = Chat::with(['buyer', 'seller', 'product.productable', 'lastMessage'])
            ->where(function ($query) {
                $query->where('buyer_id', auth()->id())
                    ->orWhere('seller_id', auth()->id());
            })
            ->withCount('unreadMessages')
            ->latest()
            ->get();

        return ChatResource::collection($chats);
    }

    public function show(Chat $chat): ChatResource
    {
        $chat->load(['product.productable', 'buyer', 'seller', 'messages']);
        return ChatResource::make($chat);
    }

    public function getOrCreate(ChatRequest $request): ChatResource
    {
        $product = Product::where('uuid', $request->product_uuid)->first();

        $chat = Chat::firstOrCreate([
            'product_id' => $product->id,
            'buyer_id' => auth()->id(),
            'seller_id' => $product->user_id
        ]);

        return ChatResource::make($chat->fresh(['product', 'buyer', 'seller']));
    }
}
