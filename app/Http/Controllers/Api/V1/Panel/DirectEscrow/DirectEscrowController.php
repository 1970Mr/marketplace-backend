<?php

namespace App\Http\Controllers\Api\V1\Panel\DirectEscrow;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\DirectEscrow\{
    CreateDirectEscrowRequest,
    DisputeRequest,
    UploadSignatureRequest
};
use App\Http\Requests\V1\Escrow\UploadReceiptsRequest;
use App\Http\Resources\V1\DirectEscrow\DirectEscrowResource;
use App\Models\Escrow;
use App\Services\DirectEscrow\DirectEscrowManagementService;
use App\Services\DirectEscrow\DirectEscrowSignatureService;
use App\Services\DirectEscrow\DirectEscrowDeliveryService;
use App\Services\DirectEscrow\DirectEscrowDisputeService;
use App\Services\Escrow\ReceiptsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DirectEscrowController extends Controller
{
    public function __construct(
        readonly private DirectEscrowManagementService $managementService,
        readonly private DirectEscrowSignatureService $signatureService,
        readonly private DirectEscrowDeliveryService $deliveryService,
        readonly private DirectEscrowDisputeService $disputeService,
        readonly private ReceiptsService $receiptsService
    ) {}

    public function getMyDirectEscrows(Request $request): JsonResponse
    {
        $escrows = $this->managementService->getMyDirectEscrows(Auth::user(), $request);
        return DirectEscrowResource::collection($escrows)->response();
    }

    public function show(Escrow $escrow): JsonResponse
    {
        $escrow->load(['offer.product', 'buyer', 'seller', 'directEscrow', 'directChat']);
        return DirectEscrowResource::make($escrow)->response();
    }

    public function store(CreateDirectEscrowRequest $request): JsonResponse
    {
        $escrow = $this->managementService->createDirectEscrow($request->validated());
        return DirectEscrowResource::make($escrow)->response();
    }

    public function uploadBuyerSignature(UploadSignatureRequest $request, Escrow $escrow): JsonResponse
    {
        $escrow = $this->signatureService->uploadBuyerSignature($escrow, $request->file('file'));
        return DirectEscrowResource::make($escrow)->response();
    }

    public function uploadSellerSignature(UploadSignatureRequest $request, Escrow $escrow): JsonResponse
    {
        $escrow = $this->signatureService->uploadSellerSignature($escrow, $request->file('file'));
        return DirectEscrowResource::make($escrow)->response();
    }

    public function uploadReceipts(UploadReceiptsRequest $request, Escrow $escrow): JsonResponse
    {
        $escrow = $this->receiptsService->uploadReceipts($escrow, $request->file('files'));
        return DirectEscrowResource::make($escrow)->response();
    }

    public function sellerConfirmDelivery(Escrow $escrow): JsonResponse
    {
        $escrow = $this->deliveryService->confirmDelivery($escrow);
        return DirectEscrowResource::make($escrow)->response();
    }

    public function buyerAcceptDelivery(Escrow $escrow): JsonResponse
    {
        $escrow = $this->deliveryService->acceptDelivery($escrow);
        return DirectEscrowResource::make($escrow)->response();
    }

    public function openDispute(DisputeRequest $request, Escrow $escrow): JsonResponse
    {
        $escrow = $this->disputeService->openDispute(
            $escrow,
            $request->validated('reason'),
            $request->validated('details')
        );
        return DirectEscrowResource::make($escrow)->response();
    }
}
