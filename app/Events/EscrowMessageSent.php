<?php

namespace App\Events;

use App\Enums\Messenger\ChatType;
use App\Http\Resources\V1\Messenger\MessageResource;
use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EscrowMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(readonly public Message $message, readonly public ChatType $chatType)
    {
        //
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PresenceChannel("escrow.chat.{$this->chatType->value}.{$this->message->chat->escrow_id}"),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'message' => MessageResource::make($this->message->load(['sender', 'offer']))
        ];
    }
}
