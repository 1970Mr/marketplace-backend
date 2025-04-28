<?php

namespace App\Http\Controllers\Api\V1\Messenger;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Messenger\ChatResource;
use App\Models\Chat;
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
//            ->paginate(10);

        return ChatResource::collection($chats);
    }

    public function show(Chat $chat): ChatResource
    {
        $chat->load(['product.productable', 'buyer', 'seller', 'messages']);
        return ChatResource::make($chat);
    }
}
