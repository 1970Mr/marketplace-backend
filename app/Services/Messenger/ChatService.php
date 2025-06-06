<?php

namespace App\Services\Messenger;

use App\Enums\Messenger\ChatType;
use App\Models\Chat;
use App\Models\Escrow;

class ChatService
{
    public function findOrCreateEscrowChat(Escrow $escrow, int $type): Chat
    {
        return Chat::firstOrCreate([
            'escrow_id' => $escrow->id,
            'type' => $type,
        ], [
            'admin_id' => $escrow->admin_id,
            'buyer_id' => $type === ChatType::ESCROW_BUYER->value ? $escrow->buyer_id : null,
            'seller_id' => $type === ChatType::ESCROW_SELLER->value ? $escrow->seller_id : null,
        ]);
    }
}
