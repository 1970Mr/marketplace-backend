<?php

namespace App\Http\Controllers\Api\V1\Panel\Escrow;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Escrow\{
    ProposeSlotsRequest,
    SelectSlotRequest,
    StoreEscrowRequest,
    UploadReceiptsRequest,
    UploadSignatureRequest
};
use App\Http\Resources\V1\Escrow\EscrowResource;
use App\Http\Resources\V1\Escrow\UnifiedEscrowResource;
use App\Models\Admin;
use App\Models\Escrow;
use App\Services\Escrow\EscrowManagementService;
use App\Services\Escrow\PaymentService;
use App\Services\Escrow\ScheduleAvailabilityService;
use App\Services\Escrow\SchedulingService;
use App\Services\Escrow\SignatureService;
use App\Services\Escrow\UnifiedEscrowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EscrowController extends Controller
{
    public function __construct(
        readonly private EscrowManagementService $managementService,
        readonly private SignatureService $signatureService,
        readonly private PaymentService $paymentService,
        readonly private SchedulingService $schedulingService,
        readonly private ScheduleAvailabilityService $scheduleAvailabilityService,
        readonly private UnifiedEscrowService $unifiedEscrowService
    ) {}

    public function getMyEscrows(Request $request): JsonResponse
    {
        $escrows = $this->unifiedEscrowService->getMyEscrows(Auth::user(), $request);
        return UnifiedEscrowResource::collection($escrows)->response();
    }

    public function show(Escrow $escrow): JsonResponse
    {
        $escrow->load(['offer.product', 'buyer', 'seller', 'admin', 'timeSlots', 'adminEscrow']);
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
        return EscrowResource::make($escrow)->response();
    }

    public function uploadSellerSignature(UploadSignatureRequest $request, Escrow $escrow): JsonResponse
    {
        $escrow = $this->signatureService->uploadSellerSignature($escrow, $request->file('file'));
        return EscrowResource::make($escrow)->response();
    }

    public function uploadReceipts(UploadReceiptsRequest $request, Escrow $escrow): JsonResponse
    {
        $escrow = $this->paymentService->uploadReceipts($escrow, $request->file('files'));
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
