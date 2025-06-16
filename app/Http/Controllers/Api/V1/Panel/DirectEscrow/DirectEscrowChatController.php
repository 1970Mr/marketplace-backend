<?php

namespace App\Http\Controllers\Api\V1\Panel\DirectEscrow;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Messenger\MessageRequest;
use App\Http\Resources\V1\Messenger\MessageResource;
use App\Models\Escrow;
use App\Services\DirectEscrow\DirectEscrowChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class DirectEscrowChatController extends Controller
{
    public function __construct(
        readonly private DirectEscrowChatService $chatService
    ) {}

    public function getMessages(Escrow $escrow): JsonResponse
    {
        $chat = $escrow->directChat;

        if (!$chat) {
            $chat = $this->chatService->createDirectEscrowChat($escrow);
        }

        $messages = $this->chatService->getMessages($chat, Auth::user());

        return MessageResource::collection($messages)->response();
    }

    public function sendMessage(MessageRequest $request, Escrow $escrow): JsonResponse
    {
        $chat = $escrow->directChat;

        if (!$chat) {
            $chat = $this->chatService->createDirectEscrowChat($escrow);
        }

        $message = $this->chatService->sendMessage(
            $chat,
            Auth::user(),
            $request->validated('content')
        );

        return MessageResource::make($message)->response();
    }
}
