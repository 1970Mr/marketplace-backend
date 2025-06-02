<?php

namespace App\Services\Messenger;

use App\Enums\Messenger\ChatType;
use App\Models\Chat;
use App\Models\Escrow;
use Illuminate\Database\Eloquent\Collection;

class ChatService
{
    public function getEscrowChats(int $escrowId): Collection
    {
        return Chat::with(['admin', 'buyer', 'seller', 'escrow'])
            ->where('escrow_id', $escrowId)
            ->withCount('unreadMessages')
            ->get();
    }

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
