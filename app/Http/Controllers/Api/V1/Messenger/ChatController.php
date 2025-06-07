<?php

namespace App\Http\Controllers\Api\V1\Messenger;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Messenger\EscrowChatRequest;
use App\Http\Resources\V1\Messenger\ChatResource;
use App\Models\Escrow;
use App\Services\Messenger\ChatService;

class ChatController extends Controller
{
    public function __construct(protected ChatService $chatService)
    {
    }

    public function findOrCreateEscrowChat(Escrow $escrow, EscrowChatRequest $request): ChatResource
    {
        $chat = $this->chatService->findOrCreateEscrowChat($escrow, $request->get('chat_type'));
        return ChatResource::make($chat->fresh(['escrow', 'admin', 'buyer', 'seller']));
    }
}
