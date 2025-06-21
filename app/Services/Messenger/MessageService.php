<?php

namespace App\Services\Messenger;

use App\Events\EscrowMessageSent;
use App\Models\Admin;
use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class MessageService
{
    public function getChatMessages(Chat $chat): Collection
    {
        /* @var User|Admin $user */
        $user = Auth::user() ?? Auth::guard('admin-api')->user();
        $this->markAllAsRead($chat, $user);
        return $chat->messages()->with(['sender', 'offer'])->get();
    }

    public function sendEscrowMessage(string $chatUuid, string $content, User|Admin $sender): Message
    {
        $chat = Chat::where('uuid', $chatUuid)->firstOrFail();

        $message = new Message([
            'content' => $content,
            'sender_type' => get_class($sender),
            'sender_id' => $sender->id,
        ]);

        $chat->messages()->save($message);

        broadcast(new EscrowMessageSent($message, $chat->type));

        return $message;
    }

    public function markAsRead(Message $message, User|Admin $currentUser): void
    {
        if (!$message->read_at && $message->sender_id !== $currentUser->id) {
            $message->update(['read_at' => now()]);
        }
    }

    public function markAllAsRead(Chat $chat, User|Admin $currentUser): void
    {
        $chat->messages()
            ->whereNull('read_at')
            ->where(function ($query) use ($currentUser) {
                $query->where('sender_type', '!=', get_class($currentUser))
                    ->orWhere('sender_id', '!=', $currentUser->id);
            })
            ->update(['read_at' => now()]);
    }
}
