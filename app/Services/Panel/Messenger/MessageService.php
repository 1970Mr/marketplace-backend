<?php

namespace App\Services\Panel\Messenger;

use App\Enums\Messenger\ChatType;
use App\Events\ChatParticipantsNotified;
use App\Events\MessageSent;
use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class MessageService
{
    public function getChatMessages(Chat $chat): Collection
    {
        return $chat->messages()->with(['sender', 'offer'])->get();
    }

    public function createMessage(string $chatUuid, int $userId, string $content): Message {
        $chat = Chat::where('uuid', $chatUuid)->firstOrFail();

        $this->activateChatIfSeller($chat, $userId);
        $this->ensureChatIsActiveForBuyer($chat, $userId);

        $message = $chat->messages()->create([
            'sender_type' => User::class,
            'sender_id' => $userId,
            'content' => $content,
        ]);

        broadcast(new MessageSent($message))->toOthers();
        broadcast(new ChatParticipantsNotified($message));

        return $message;
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
        if (!$message->read_at && $message->sender_id !== $currentUserId && $message->chat->type === ChatType::USER_TO_USER) {
            $message->update(['read_at' => now()]);
        }
    }
}
