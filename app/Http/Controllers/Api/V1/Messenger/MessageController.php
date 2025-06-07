<?php

namespace App\Http\Controllers\Api\V1\Messenger;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Messenger\EscrowMessageRequest;
use App\Http\Resources\V1\Messenger\MessageResource;
use App\Models\Chat;
use App\Models\Message;
use App\Services\Messenger\MessageService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class MessageController extends Controller
{
    public function __construct(protected MessageService $messageService)
    {
    }

    public function index(Chat $chat): AnonymousResourceCollection
    {
        $messages = $this->messageService->getChatMessages($chat);
        return MessageResource::collection($messages);
    }

    public function store(EscrowMessageRequest $request): MessageResource
    {
        $sender = auth()->user() ?? auth()->guard('admin-api')->user();
        $message = $this->messageService->sendEscrowMessage(
            $request->get('chat_uuid'),
            $request->get('content'),
            $sender
        );

        return MessageResource::make($message->fresh(['sender']));
    }

    public function markAsRead(Message $message): Response
    {
        $this->messageService->markMessageAsRead($message, auth()->id());
        return response()->noContent();
    }
}
