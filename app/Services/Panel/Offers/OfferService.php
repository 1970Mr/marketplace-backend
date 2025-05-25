<?php

namespace App\Services\Panel\Offers;

use App\Enums\Escrow\EscrowStatus;
use App\Enums\Messenger\MessageType;
use App\Enums\Offers\OfferType;
use App\Events\ChatParticipantsNotified;
use App\Events\MessageSent;
use App\Models\Chat;
use App\Models\Offer;
use App\Models\Products\Product;
use App\Services\Escrow\EscrowManagementService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class OfferService
{
    public function __construct(readonly private EscrowManagementService $escrowManagementService)
    {
    }

    public function getOffersForUser(Request $request, int $userId, string $userColumn = 'seller_id', ?OfferType $offerType = null): LengthAwarePaginator
    {
        $query = Offer::where($userColumn, $userId)
            ->with(['product', 'chat', 'buyer', 'seller']);

        if ($offerType) {
            $query->where('status', $offerType->value);
        }

        $this->applySearch($query, $request);
        $this->applyFilters($query, $request);

        return $query->latest()
            ->paginate($request->input('per_page', 10));
    }

    private function applySearch(Builder $query, Request $request): void
    {
        $query->when($request->filled('search'), function (Builder $q) use ($request) {
            $q->whereHas('product', function (Builder $sub) use ($request) {
                $sub->whereLike('title', "%$request->search%");
            });
        });
    }

    private function applyFilters(Builder $query, Request $request): void
    {
        $query->when($request->filled('status'),
            fn($q) => $q->where('status', $request->status)
        );
    }

    public function createOffer(string $productUuid, int $buyerId, float $amount): Offer
    {
        $product = Product::where('uuid', $productUuid)->firstOrFail();
        $sellerId = $product->user_id;

        return $this->handleCreateOffer($product->id, $buyerId, $sellerId, $amount);
    }

    private function handleCreateOffer(int $productId, int $buyerId, int $sellerId, float $amount): Offer
    {
        $chat = $this->firstOrCreateChat($productId, $buyerId, $sellerId);

        $offer = $chat->offers()->create([
            'buyer_id' => $buyerId,
            'seller_id' => $sellerId,
            'product_id' => $productId,
            'amount' => $amount,
        ]);

        $this->createOfferMessage($chat, $buyerId, $offer->id);

        return $offer->fresh(['product', 'chat', 'buyer', 'seller']);
    }

    private function firstOrCreateChat(int $productId, int $buyerId, int $sellerId): Chat
    {
        return Chat::firstOrCreate([
            'product_id' => $productId,
            'buyer_id' => $buyerId,
            'seller_id' => $sellerId,
        ]);
    }

    private function createOfferMessage(Chat $chat, int $buyerId, int $offerId): void
    {
        $message = $chat->messages()->create([
            'content' => 'New offer submitted',
            'type' => MessageType::OFFER,
            'user_id' => $buyerId,
            'offer_id' => $offerId,
        ]);

        broadcast(new MessageSent($message->fresh(['user', 'offer'])))->toOthers();
        broadcast(new ChatParticipantsNotified($message));
    }

    private function checkCanDeleteEscrow(Offer $offer): bool
    {
        if (!$offer->escrow()->exists()) {
            return false;
        }

        if ($offer->escrow->status !== EscrowStatus::PENDING) {
            throw ValidationException::withMessages([
                'escrow_is_active' => 'This Offer has an active Escrow you can\'t delete it'
            ]);
        }

        return true;
    }

    private function handleCreateEscrowWhenAcceptOffer(Offer $offer, int $status): void {
        if ($status === OfferType::ACCEPTED->value) {
            $this->escrowManagementService->createEscrow([
                'offer_id' => $offer->id,
                'buyer_id' => $offer->buyer_id,
                'seller_id' => $offer->seller_id,
            ]);
        }
    }

    private function handleDeleteEscrowWhenRejectOffer(Offer $offer, int $status): void {
        if ($status === OfferType::REJECTED->value && $this->checkCanDeleteEscrow($offer)) {
            $offer->escrow()->delete();
        }
    }

    public function changeStatus(Offer $offer, int $status): Offer
    {
        $this->handleDeleteEscrowWhenRejectOffer($offer, $status);
        $this->handleCreateEscrowWhenAcceptOffer($offer, $status);

        $offer->update(['status' => $status]);
        return $offer->fresh();
    }

    public function deleteOffer(Offer $offer, int $buyerId): void
    {
        abort_if($offer->buyer_id !== $buyerId, 401);

        if ($this->checkCanDeleteEscrow($offer)) {
            $offer->escrow()->delete();
        }

        $offer->delete();
    }
}
