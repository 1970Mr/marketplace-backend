<?php

namespace App\Http\Controllers\Api\V1\Messenger;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Messenger\ChatRequest;
use App\Http\Resources\V1\Messenger\ChatResource;
use App\Models\Chat;
use App\Services\Messenger\ChatService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ChatController extends Controller
{
    public function __construct(protected ChatService $chatService)
    {
    }

    public function index(): AnonymousResourceCollection
    {
        $chats = $this->chatService->getUserChats(auth()->id());
        return ChatResource::collection($chats);
    }

    public function show(Chat $chat): ChatResource
    {
        $chat->load(['product.productable', 'buyer', 'seller', 'messages']);
        return ChatResource::make($chat);
    }

    public function getOrCreate(ChatRequest $request): ChatResource
    {
        $chat = $this->chatService->findOrCreateChat($request->product_uuid, auth()->id());
        return ChatResource::make($chat->fresh(['product', 'buyer', 'seller']));
    }
}
