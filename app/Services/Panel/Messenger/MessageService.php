<?php

namespace App\Services\Panel\Messenger;

use App\Models\Chat;
use App\Models\Message;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class MessageService
{
    public function getChatMessages(Chat $chat): Collection
    {
        return $chat->messages()->with(['user', 'offer'])->get();
    }

    public function createMessage(string $chatUuid, int $userId, string $content): Message {
        $chat = Chat::where('uuid', $chatUuid)->firstOrFail();

        $this->activateChatIfSeller($chat, $userId);
        $this->ensureChatIsActiveForBuyer($chat, $userId);

        return $chat->messages()->create([
            'user_id' => $userId,
            'content' => $content,
        ]);
    }

    private function activateChatIfSeller(Chat $chat, int $userId): void
    {
        if ($chat->seller_id === $userId && $chat->product) {
            $chat->product->update([
                'allow_buyer_message' => true,
            ]);
        }
    }

    private function ensureChatIsActiveForBuyer(Chat $chat, int $userId): void
    {
        if ($chat->buyer_id === $userId && !$chat->product?->allow_buyer_message) {
            throw ValidationException::withMessages([
                'message' => 'Sending message for this product is not active.',
            ]);
        }
    }

    public function markMessageAsRead(Message $message, int $currentUserId): void
    {
        if (!$message->read_at && $message->user_id !== $currentUserId) {
            $message->update(['read_at' => now()]);
        }
    }
}
