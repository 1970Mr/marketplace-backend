<?php

namespace App\Http\Controllers\Api\V1\Offers;

use App\Enums\Messenger\MessageType;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Offers\ChangeStatusRequest;
use App\Http\Requests\V1\Offers\OfferRequest;
use App\Http\Resources\V1\Offers\OfferResource;
use App\Models\Chat;
use App\Models\Offer;
use App\Models\Products\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OfferController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $offers = Offer::whereSellerId(auth()->id())
            ->with(['product', 'chat', 'buyer'])
            ->latest()
            ->paginate(10);

        return OfferResource::collection($offers);
    }

    public function store(OfferRequest $request): JsonResponse
    {
        $product = Product::where('uuid', $request->product_uuid)->first();

        $chat = Chat::firstOrCreate([
            'product_id' => $product->id,
            'buyer_id' => auth()->id(),
            'seller_id' => $product->user_id
        ]);

        $offer = $chat->offers()->create([
            'buyer_id' => auth()->id(),
            'seller_id' => $product->user_id,
            'product_id' => $product->id,
            'amount' => $request->amount,
        ]);

        // Create system message
        $chat->messages()->create([
            'content' => 'New offer submitted',
            'type' => MessageType::OFFER,
            'user_id' => auth()->id(),
            'offer_id' => $offer->id,
        ]);

        return response()->json([
            'message' => 'Offer submitted successfully',
            'data' => OfferResource::make($offer->fresh(['product', 'chat', 'buyer', 'seller']))
        ], 201);
    }

    public function changeStatus(ChangeStatusRequest $request, Offer $offer): JsonResponse
    {
        $offer->update($request->validated());

        return response()->json([
            'message' => 'Offer updated',
            'data' => OfferResource::make($offer->fresh())
        ]);
    }
}
