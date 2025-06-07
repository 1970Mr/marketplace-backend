<?php

namespace App\Services\Messenger;

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
            'buyer_id' => $escrow->buyer_id,
            'seller_id' => $escrow->seller_id,
        ]);
    }
}
