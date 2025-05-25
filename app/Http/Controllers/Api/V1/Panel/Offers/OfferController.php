<?php

namespace App\Http\Controllers\Api\V1\Panel\Offers;

use App\Enums\Escrow\EscrowStatus;
use App\Enums\Offers\OfferType;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Offers\ChangeStatusRequest;
use App\Http\Requests\V1\Offers\OfferRequest;
use App\Http\Resources\V1\Offers\OfferResource;
use App\Models\Offer;
use App\Services\Panel\Offers\OfferService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class OfferController extends Controller
{
    public function __construct(protected OfferService $offerService)
    {
    }

    public function sellerOffers(Request $request): AnonymousResourceCollection
    {
        $offers = $this->offerService->getOffersForUser($request, auth()->id());
        return OfferResource::collection($offers);
    }

    public function acceptedSellerOffers(Request $request): AnonymousResourceCollection
    {
        $offers = $this->offerService->getOffersForUser($request, auth()->id(), 'seller_id', OfferType::ACCEPTED);
        return OfferResource::collection($offers);
    }

    public function buyerOffers(Request $request): AnonymousResourceCollection
    {
        $offers = $this->offerService->getOffersForUser($request, auth()->id(), 'buyer_id');
        return OfferResource::collection($offers);
    }

    public function acceptedBuyerOffers(Request $request): AnonymousResourceCollection
    {
        $offers = $this->offerService->getOffersForUser($request, auth()->id(), 'buyer_id', OfferType::ACCEPTED);
        return OfferResource::collection($offers);
    }

    public function store(OfferRequest $request): JsonResponse
    {
        $offer = $this->offerService->createOffer($request->product_uuid, auth()->id(), $request->amount);

        return response()->json([
            'message' => 'Offer submitted successfully',
            'data' => OfferResource::make($offer)
        ], 201);
    }

    public function changeStatus(ChangeStatusRequest $request, Offer $offer): JsonResponse
    {
        $this->offerService->changeStatus($offer, $request->get('status'));
        return response()->json([
            'message' => 'Offer status updated',
            'data' => OfferResource::make($offer->fresh())
        ]);
    }

    public function destroy(Offer $offer): Response
    {
        $this->offerService->deleteOffer($offer, Auth::id());
        return response()->noContent();
    }
}
