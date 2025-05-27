<?php

namespace App\Http\Controllers\Api\V1\Panel\Escrow;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Services\Escrow\EscrowManagementService;
use App\Services\Escrow\PaymentService;
use App\Services\Escrow\ScheduleAvailabilityService;
use App\Services\Escrow\SchedulingService;
use App\Services\Escrow\SignatureService;
use App\Http\Requests\V1\Escrow\{
    GetUserEscrowsRequest,
    ProposeSlotsRequest,
    SelectSlotRequest,
    StoreEscrowRequest,
    UploadReceiptsRequest,
    UploadSignatureRequest
};
use App\Http\Resources\V1\Escrow\{
    EscrowResource,
    TimeSlotResource
};
use App\Models\Escrow;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Auth;

class EscrowController extends Controller
{
    public function __construct(
        readonly private EscrowManagementService $managementService,
        readonly private SignatureService $signatureService,
        readonly private PaymentService $paymentService,
        readonly private SchedulingService $schedulingService,
        readonly private ScheduleAvailabilityService $scheduleAvailabilityService
    )
    {
    }

    public function getUserEscrows(GetUserEscrowsRequest $request): ResourceCollection
    {
        $escrows = $this->managementService->getUserEscrows(Auth::user(), $request->validated());
        return EscrowResource::collection($escrows);
    }

    public function show(Escrow $escrow): EscrowResource
    {
        $escrow->load(['offer.product', 'buyer', 'seller', 'admin']);
        return new EscrowResource($escrow);
    }

    public function store(StoreEscrowRequest $request): JsonResponse
    {
        $escrow = $this->managementService->createEscrow($request->validated());
        return response()->json(new EscrowResource($escrow), 201);
    }

    public function uploadBuyerSignature(UploadSignatureRequest $request, Escrow $escrow): EscrowResource
    {
        $escrow = $this->signatureService->uploadBuyerSignature($escrow, $request->file('file'));
        $escrow->load(['offer.product', 'buyer', 'seller', 'admin']);
        return new EscrowResource($escrow);
    }

    public function uploadSellerSignature(UploadSignatureRequest $request, Escrow $escrow): EscrowResource
    {
        $escrow = $this->signatureService->uploadSellerSignature($escrow, $request->file('file'));
        $escrow->load(['offer.product', 'buyer', 'seller', 'admin']);
        return new EscrowResource($escrow);
    }

    public function uploadReceipts(UploadReceiptsRequest $request, Escrow $escrow): EscrowResource
    {
        $escrow = $this->paymentService->uploadReceipts($escrow, $request->file('files'));
        $escrow->load(['offer.product', 'buyer', 'seller', 'admin']);
        return new EscrowResource($escrow);
    }

    public function getAdminAvailability(Admin $admin): JsonResponse
    {
        $slots = $this->scheduleAvailabilityService->getNextAvailableSlots($admin);
        return response()->json($slots);
    }

    public function proposeSlots(ProposeSlotsRequest $request, Escrow $escrow): ResourceCollection
    {
        $slots = $this->schedulingService->proposeSlots(
            $escrow,
            $request->validated('slot_ids')
        );
        return TimeSlotResource::collection($slots);
    }

    public function selectSlot(SelectSlotRequest $request, Escrow $escrow): TimeSlotResource
    {
        $slot = $this->schedulingService->selectSlot(
            $escrow,
            $request->validated('slot_id')
        );
        return new TimeSlotResource($slot);
    }

    public function rejectScheduling(Escrow $escrow): EscrowResource
    {
        $escrow = $this->schedulingService->rejectScheduling($escrow);
        return new EscrowResource($escrow);
    }
}
