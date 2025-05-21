<?php

namespace App\Http\Controllers\Api\V1\Escrow;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Escrow\{ConfirmPaymentRequest,
    ProposeSlotsRequest,
    ReleaseFundsRequest,
    SelectSlotRequest,
    StoreEscrowRequest,
    UploadReceiptsRequest,
    UploadSignatureRequest,
};
use App\Http\Resources\V1\Escrow\EscrowResource;
use App\Http\Resources\V1\Escrow\TimeSlotResource;
use App\Models\Escrow;
use App\Services\Escrow\EscrowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Auth;

class EscrowController extends Controller
{
    public function __construct(readonly private EscrowService $service)
    {
    }

    public function getAdminEscrows(): ResourceCollection
    {
        $escrows = Auth::guard('admin-api')->user()?->escrows()->with(['offer', 'buyer', 'seller', 'admin'])->paginate(10);
        return EscrowResource::collection($escrows);
    }

    public function getUserEscrows(Request $request): ResourceCollection
    {
        $escrows = Auth::user()
            ?->escrows()
            ->with(['offer.product', 'buyer', 'seller', 'admin'])
            ->filterByProductTitle($request->get('search'))
            ->filterBy('status', $request->get('status'))
            ->filterBy('phase', $request->get('phase'))
            ->filterBy('stage', $request->get('stage'))
            ->paginate($request->get('per_page', 10));

        return EscrowResource::collection($escrows);
    }

    public function show(Escrow $escrow): EscrowResource
    {
        $escrow->load(['offer', 'buyer', 'seller', 'admin']);
        return new EscrowResource($escrow);
    }

    public function store(StoreEscrowRequest $request): JsonResponse
    {
        $escrow = $this->service->createEscrow($request->validated());
        return response()->json(new EscrowResource($escrow), 201);
    }

    public function accept(Escrow $escrow): EscrowResource
    {
        $adminId = Auth::guard('admin-api')->id();
        $escrow = $this->service->acceptEscrow($escrow, $adminId);
        return new EscrowResource($escrow);
    }

    public function uploadBuyerSignature(UploadSignatureRequest $request, Escrow $escrow): EscrowResource
    {
        $escrow = $this->service->uploadBuyerSignature($escrow, $request->file('file'));
        return new EscrowResource($escrow);
    }

    public function uploadSellerSignature(UploadSignatureRequest $request, Escrow $escrow): EscrowResource
    {
        $escrow = $this->service->uploadSellerSignature($escrow, $request->file('file'));
        return new EscrowResource($escrow);
    }

    public function uploadReceipts(UploadReceiptsRequest $request, Escrow $escrow): EscrowResource
    {
        $escrow = $this->service->uploadReceipts($escrow, $request->file('files'));
        return new EscrowResource($escrow);
    }

    public function confirmPayment(ConfirmPaymentRequest $request, Escrow $escrow): EscrowResource
    {
        $escrow = $this->service->confirmPayment(
            $escrow,
            $request->validated('amount'),
            $request->validated('method')
        );
        return new EscrowResource($escrow);
    }

    public function proposeSlots(ProposeSlotsRequest $request, Escrow $escrow): ResourceCollection
    {
        $slots = $this->service->proposeSlots(
            $escrow,
            $request->validated('weekdays'),
            $request->validated('times')
        );
        return TimeSlotResource::collection($slots);
    }

    public function selectSlot(SelectSlotRequest $request, Escrow $escrow): TimeSlotResource
    {
        $slot = $this->service->selectSlot(
            $escrow,
            $request->validated('slot_id')
        );
        return new TimeSlotResource($slot);
    }

    public function rejectScheduling(Escrow $escrow): EscrowResource
    {
        $escrow = $this->service->rejectScheduling($escrow);
        return new EscrowResource($escrow);
    }

    public function confirmDelivery(Escrow $escrow): EscrowResource
    {
        $escrow = $this->service->confirmDelivery($escrow);
        return new EscrowResource($escrow);
    }

    public function releaseFunds(ReleaseFundsRequest $request, Escrow $escrow): EscrowResource
    {
        $escrow = $this->service->releaseFunds(
            $escrow,
            $request->validated('amount'),
            $request->validated('method')
        );
        return new EscrowResource($escrow);
    }

    public function cancel(Escrow $escrow): EscrowResource
    {
        $escrow = $this->service->cancel($escrow);
        return new EscrowResource($escrow);
    }

    public function refund(Escrow $escrow): EscrowResource
    {
        $escrow = $this->service->refund($escrow);
        return new EscrowResource($escrow);
    }
}
