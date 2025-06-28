<?php

namespace App\Services\Panel\Dashboard;

use App\Enums\Escrow\DirectEscrowStatus;
use App\Enums\Escrow\EscrowStatus;
use App\Enums\Offers\OfferType;
use App\Models\Escrow;
use App\Models\Message;
use App\Models\Offer;
use App\Models\Products\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

readonly class DashboardService
{
    public function getDashboardData(User $user): array
    {
        return [
            'offers' => $this->getOffersData($user),
            'listings' => $this->getListingsData($user),
            'escrows' => $this->getEscrowsData($user),
            'earnings' => $this->getEarningsData($user),
            'recent_messages' => $this->getRecentMessages($user),
            'recent_escrows' => $this->getRecentEscrows($user),
        ];
    }

    private function getOffersData(User $user): array
    {
        $buyingOffers = $this->countUserOffers($user->id, 'buyer_id');
        $sellingOffers = $this->countUserOffers($user->id, 'seller_id');

        return [
            'buying' => $buyingOffers,
            'selling' => $sellingOffers,
        ];
    }

    private function countUserOffers(int $userId, string $userColumn): int
    {
        return Offer::where($userColumn, $userId)
            ->where('status', OfferType::PENDING)
            ->count();
    }

    private function getListingsData(User $user): array
    {
        $activeListings = Product::where('user_id', $user->id)->published()->count();

        return [
            'active' => $activeListings,
        ];
    }

    private function getEscrowsData(User $user): array
    {
        $adminEscrows = Escrow::where(static function (Builder $query) use ($user) {
            $query->where('buyer_id', $user->id)
                ->orWhere('seller_id', $user->id);
        })
            ->whereHas('adminEscrow', static function (Builder $query) {
                $query->whereIn('status', [EscrowStatus::PENDING->value, EscrowStatus::ACTIVE->value]);
            })
            ->count();

        $directEscrows = Escrow::where(static function (Builder $query) use ($user) {
            $query->where('buyer_id', $user->id)
                ->orWhere('seller_id', $user->id);
        })
            ->whereHas('directEscrow', static function (Builder $query) {
                $query->whereIn('status', [DirectEscrowStatus::PENDING->value, DirectEscrowStatus::ACTIVE->value]);
            })
            ->count();

        return [
            'active' => $adminEscrows + $directEscrows,
        ];
    }

    private function getEarningsData(User $user): array
    {
        $adminEarnings = Escrow::where('seller_id', $user->id)
            ->whereHas('adminEscrow', static function (Builder $query) {
                $query->where('status', EscrowStatus::COMPLETED->value);
            })
            ->sum('amount_released');

        $directEarnings = Escrow::where('seller_id', $user->id)
            ->whereHas('directEscrow', static function (Builder $query) {
                $query->where('status', EscrowStatus::COMPLETED->value);
            })
            ->sum('amount_released');

        return [
            'total' => ($adminEarnings ?? 0) + ($directEarnings ?? 0),
        ];
    }

    private function getRecentMessages(User $user): array
    {
        $messages = Message::whereHas('chat', static function (Builder $query) use ($user) {
            $query->where('buyer_id', $user->id)
                ->orWhere('seller_id', $user->id);
        })
            ->where('sender_id', '!=', $user->id)
            ->with(['chat.product', 'chat.escrow.offer.product', 'sender'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(static function ($message) {
                return [
                    'id' => $message->id,
                    'uuid' => $message->uuid,
                    'content' => $message->content,
                    'sender_name' => $message->sender->name ?? 'Unknown',
                    'product_title' => $message->chat->product->title ?? $message->chat->escrow->offer->product->title ?? 'Unknown Product',
                    'product_uuid' => $message->chat->product->uuid ?? null,
                    'chat_uuid' => $message->chat->uuid,
                    'chat_type' => $message->chat->type->value,
                    'escrow_uuid' => $message->chat->escrow?->uuid,
                    'created_at' => $message->created_at,
                    'is_read' => !is_null($message->read_at),
                ];
            });

        return $messages->toArray();
    }

    private function getRecentEscrows(User $user): array
    {
        $escrows = Escrow::where(static function (Builder $query) use ($user) {
            $query->where('buyer_id', $user->id)
                ->orWhere('seller_id', $user->id);
        })
            ->with(['offer.product', 'buyerChat', 'sellerChat', 'directChat', 'adminEscrow', 'directEscrow'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($escrow) use ($user) {
                $hasUnreadMessages = $this->checkEscrowUnreadMessages($escrow, $user);

                return [
                    'id' => $escrow->id,
                    'uuid' => $escrow->uuid,
                    'product_title' => $escrow->offer->product->title ?? 'Unknown Product',
                    'product_uuid' => $escrow->offer->product->uuid ?? null,
                    'amount' => $escrow->offer->amount ?? 0,
                    'type' => $escrow->type->value,
                    'status' => $escrow->escrowDetails()->status->value,
                    'created_at' => $escrow->created_at,
                    'has_unread_messages' => $hasUnreadMessages,
                ];
            });

        return $escrows->toArray();
    }

    private function checkEscrowUnreadMessages(Escrow $escrow, User $user): bool
    {
        // Get the appropriate chat based on user role and escrow type
        $chat = null;

        if ($escrow->isDirectEscrow()) {
            $chat = $escrow->directChat;
        } else {
            $chat = $escrow->buyer_id === $user->id ? $escrow->buyerChat : $escrow->sellerChat;
        }

        if (!$chat) {
            return false;
        }

        // Check if there are unread messages in this chat
        return Message::where('chat_id', $chat->id)
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->exists();
    }
}
