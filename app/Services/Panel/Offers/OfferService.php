<?php

namespace App\Services\Panel\Offers;

use App\Enums\Messenger\MessageType;
use App\Models\Chat;
use App\Models\Offer;
use App\Models\Products\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class OfferService
{
    public function getOffersForUser(Request $request, int $userId, string $userColumn = 'seller_id'): LengthAwarePaginator
    {
        $query = Offer::where($userColumn, $userId)
            ->with(['product', 'chat', 'buyer', 'seller']);

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
        $chat->messages()->create([
            'content' => 'New offer submitted',
            'type' => MessageType::OFFER,
            'user_id' => $buyerId,
            'offer_id' => $offerId,
        ]);
    }

    public function deleteOffer(Offer $offer, int $buyerId): void
    {
        abort_if($offer->buyer_id !== $buyerId, 401);
        $offer->delete();
    }
}
