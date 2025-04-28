<?php

namespace App\Http\Controllers\Api\V1\Messenger;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Messenger\MessageRequest;
use App\Http\Resources\V1\Messenger\MessageResource;
use App\Models\Chat;
use App\Models\Message;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class MessageController extends Controller
{
    public function index(Chat $chat): AnonymousResourceCollection
    {
        $messages = $chat->messages()
            ->with(['user', 'offer'])
            ->get();

        return MessageResource::collection($messages);
    }

    public function store(MessageRequest $request): MessageResource
    {
        $chat = Chat::where('uuid', $request->get('chat_uuid'))->firstOrFail();

        $message = $chat->messages()->create([
            'user_id' => auth()->id(),
            'content' => $request->get('content'),
        ]);

        return MessageResource::make($message->fresh(['user', 'offer']));
    }

    public function markAsRead(Message $message): Response
    {
        if (!$message->read_at && $message->user_id !== auth()->id()) {
            $message->update(['read_at' => now()]);
        }

        return response()->noContent();
    }
}
