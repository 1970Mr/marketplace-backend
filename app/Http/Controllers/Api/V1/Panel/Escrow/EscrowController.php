<?php

namespace App\Http\Controllers\Api\V1\Panel\Escrow;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Escrow\{
    GetUserEscrowsRequest,
    ProposeSlotsRequest,
    SelectSlotRequest,
    StoreEscrowRequest,
    UploadReceiptsRequest,
    UploadSignatureRequest
};
use App\Http\Resources\V1\Escrow\EscrowResource;
use App\Models\Admin;
use App\Models\Escrow;
use App\Services\Escrow\EscrowManagementService;
use App\Services\Escrow\PaymentService;
use App\Services\Escrow\ScheduleAvailabilityService;
use App\Services\Escrow\SchedulingService;
use App\Services\Escrow\SignatureService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class EscrowController extends Controller
{
    public function __construct(
        readonly private EscrowManagementService $managementService,
        readonly private SignatureService $signatureService,
        readonly private PaymentService $paymentService,
        readonly private SchedulingService $schedulingService,
        readonly private ScheduleAvailabilityService $scheduleAvailabilityService
    ) {}

    public function getUserEscrows(GetUserEscrowsRequest $request): JsonResponse
    {
        $escrows = $this->managementService->getUserEscrows(Auth::user(), $request->validated());
        return EscrowResource::collection($escrows)->response();
    }

    public function show(Escrow $escrow): JsonResponse
    {
        $escrow->load(['offer.product', 'buyer', 'seller', 'admin', 'timeSlots']);
        return EscrowResource::make($escrow)->response();
    }

    public function store(StoreEscrowRequest $request): JsonResponse
    {
        $escrow = $this->managementService->createEscrow($request->validated());
        return EscrowResource::make($escrow)->response();
    }

    public function uploadBuyerSignature(UploadSignatureRequest $request, Escrow $escrow): JsonResponse
    {
        $escrow = $this->signatureService->uploadBuyerSignature($escrow, $request->file('file'));
        $escrow->load(['offer.product', 'buyer', 'seller', 'admin']);
        return EscrowResource::make($escrow)->response();
    }

    public function uploadSellerSignature(UploadSignatureRequest $request, Escrow $escrow): JsonResponse
    {
        $escrow = $this->signatureService->uploadSellerSignature($escrow, $request->file('file'));
        $escrow->load(['offer.product', 'buyer', 'seller', 'admin']);
        return EscrowResource::make($escrow)->response();
    }

    public function uploadReceipts(UploadReceiptsRequest $request, Escrow $escrow): JsonResponse
    {
        $escrow = $this->paymentService->uploadReceipts($escrow, $request->file('files'));
        $escrow->load(['offer.product', 'buyer', 'seller', 'admin']);
        return EscrowResource::make($escrow)->response();
    }

    public function getAdminAvailability(Admin $admin): JsonResponse
    {
        $slots = $this->scheduleAvailabilityService->getNextAvailableSlots($admin);
        return response()->json($slots);
    }

    public function proposeSlots(ProposeSlotsRequest $request, Escrow $escrow): JsonResponse
    {
        $escrow = $this->schedulingService->proposeSlots(
            $escrow,
            $request->validated('slot_ids')
        );
        return EscrowResource::make($escrow)->response();
    }

    public function selectSlot(SelectSlotRequest $request, Escrow $escrow): JsonResponse
    {
        $escrow = $this->schedulingService->selectSlot(
            $escrow,
            $request->validated('slot_id')
        );
        return EscrowResource::make($escrow)->response();
    }

    public function rejectScheduling(Escrow $escrow): JsonResponse
    {
        $escrow = $this->schedulingService->rejectScheduling($escrow);
        return EscrowResource::make($escrow)->response();
    }
}
