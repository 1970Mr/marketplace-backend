<?php

namespace App\Http\Controllers\Api\V1\Panel\Messenger;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Messenger\MessageRequest;
use App\Http\Resources\V1\Messenger\MessageResource;
use App\Models\Chat;
use App\Models\Message;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class MessageController extends Controller
{
    public function __construct(protected \App\Services\Panel\Messenger\MessageService $messageService)
    {
    }

    public function index(Chat $chat): AnonymousResourceCollection
    {
        $messages = $this->messageService->getChatMessages($chat);
        return MessageResource::collection($messages);
    }

    public function store(MessageRequest $request): MessageResource
    {
        $message = $this->messageService->createMessage(
            $request->get('chat_uuid'),
            auth()->id(),
            $request->get('content')
        );

        return MessageResource::make($message->fresh(['user', 'offer']));
    }

    public function markAsRead(Message $message): Response
    {
        $this->messageService->markMessageAsRead($message, auth()->id());
        return response()->noContent();
    }
}
