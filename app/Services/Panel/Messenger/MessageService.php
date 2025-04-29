<?php

namespace App\Services\Panel\Messenger;

use App\Models\Chat;
use App\Models\Message;
use Illuminate\Database\Eloquent\Collection;

class MessageService
{
    public function getChatMessages(Chat $chat): Collection
    {
        return $chat->messages()->with(['user', 'offer'])->get();
    }

    public function createMessage(string $chatUuid, int $userId, string $content): Message {
        $chat = Chat::where('uuid', $chatUuid)->firstOrFail();

        return $chat->messages()->create([
            'user_id' => $userId,
            'content' => $content,
        ]);
    }

    public function markMessageAsRead(Message $message, int $currentUserId): void
    {
        if (!$message->read_at && $message->user_id !== $currentUserId) {
            $message->update(['read_at' => now()]);
        }
    }
}
