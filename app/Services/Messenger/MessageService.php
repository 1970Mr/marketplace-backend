<?php

namespace App\Services\Messenger;

use App\Enums\Messenger\ChatType;
use App\Events\EscrowMessageSent;
use App\Models\Admin;
use App\Models\Chat;
use App\Models\Escrow;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class MessageService
{
    public function getChatMessages(Chat $chat): Collection
    {
        return $chat->messages()->with(['sender', 'offer'])->get();
    }

    public function sendEscrowMessage(string $chatUuid, int $type, User|Admin $sender, string $content): Message
    {
        $chat = Chat::where('uuid', $chatUuid)->firstOrFail();

        $message = new Message([
            'content' => $content,
            'sender_type' => get_class($sender),
            'sender_id' => $sender->id,
        ]);

        $chat->messages()->save($message);

        broadcast(new EscrowMessageSent($message, $type))->toOthers();

        return $message;
    }

    public function markMessageAsRead(Message $message, int $currentUserId): void
    {
        if (!$message->read_at && $message->sender_id !== $currentUserId &&
            ($message->chat->type === ChatType::ESCROW_SELLER || $message->chat->type === ChatType::ESCROW_BUYER)) {
            $message->update(['read_at' => now()]);
        }
    }
}
