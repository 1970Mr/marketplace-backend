<?php

namespace App\Services\DirectEscrow;

use App\Enums\Messenger\ChatType;
use App\Models\Chat;
use App\Models\Escrow;
use App\Models\Message;
use App\Models\User;

class DirectEscrowChatService
{
    public function createDirectEscrowChat(Escrow $escrow): Chat
    {
        return Chat::create([
            'escrow_id' => $escrow->id,
            'type' => ChatType::DIRECT_ESCROW,
        ]);
    }

    public function sendMessage(Chat $chat, User $sender, string $message): Message
    {
        return Message::create([
            'chat_id' => $chat->id,
            'sender_id' => $sender->id,
            'message' => $message,
        ]);
    }

    public function getMessages(Chat $chat, User $user)
    {
        // Mark messages as read for this user
        $chat->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return $chat->messages()
            ->with(['sender'])
            ->latest()
            ->get();
    }
}
