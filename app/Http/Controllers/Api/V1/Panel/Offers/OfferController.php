<?php

namespace App\Http\Controllers\Api\V1\Panel\Offers;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Offers\ChangeStatusRequest;
use App\Http\Requests\V1\Offers\OfferRequest;
use App\Http\Resources\V1\Offers\OfferResource;
use App\Models\Offer;
use App\Services\Panel\Offers\OfferService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OfferController extends Controller
{
    public function __construct(protected OfferService $offerService)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $offers = $this->offerService->getOffersForSeller($request, auth()->id());
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
        $offer->update($request->validated());

        return response()->json([
            'message' => 'Offer status updated',
            'data' => OfferResource::make($offer->fresh())
        ]);
    }
}
