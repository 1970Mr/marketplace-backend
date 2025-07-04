<?php

namespace App\Http\Controllers\Api\V1\Admin\Escrow;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Http\Requests\V1\Escrow\{
    CancelEscrowRequest,
    ConfirmPaymentRequest,
    RefundEscrowRequest,
    CompleteEscrowRequest
};
use App\Http\Resources\V1\Escrow\EscrowResource;
use App\Models\Escrow;
use App\Services\Escrow\DeliveryService;
use App\Services\Escrow\EscrowManagementService;
use App\Services\Escrow\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Auth;

class EscrowController extends Controller
{
    public function __construct(
        readonly private EscrowManagementService $managementService,
        readonly private PaymentService $paymentService,
        readonly private DeliveryService $deliveryService
    ) {}

    public function getUnassignedEscrows(Request $request): ResourceCollection
    {
        $escrows = $this->managementService->getUnassignedEscrows($request);
        return EscrowResource::collection($escrows);
    }

    public function getMyEscrows(Request $request): ResourceCollection
    {
        $escrows = $this->managementService->getMyEscrows(Auth::guard('admin-api')->user(), $request);
        return EscrowResource::collection($escrows);
    }

    public function show(Escrow $escrow): EscrowResource
    {
        $escrow->load(['offer.product', 'buyer', 'seller', 'admin', 'timeSlots', 'adminEscrow']);
        return new EscrowResource($escrow);
    }

    public function assignAgent(Escrow $escrow, Admin $admin): EscrowResource
    {
        $escrow = $this->managementService->assignAgent($escrow, $admin->id);
        return new EscrowResource($escrow);
    }

    public function confirmPayment(ConfirmPaymentRequest $request, Escrow $escrow): EscrowResource
    {
        $escrow = $this->paymentService->confirmPayment(
            $escrow,
            $request->validated('amount'),
            $request->validated('method')
        );
        return new EscrowResource($escrow);
    }

    public function confirmDelivery(Escrow $escrow): EscrowResource
    {
        $escrow = $this->deliveryService->confirmDelivery($escrow);
        return new EscrowResource($escrow);
    }

    public function complete(CompleteEscrowRequest $request, Escrow $escrow): EscrowResource
    {
        $escrow = $this->managementService->completeEscrow(
            $escrow,
            $request->validated('amount'),
            $request->validated('method')
        );
        return new EscrowResource($escrow);
    }

    public function cancel(Escrow $escrow, CancelEscrowRequest $request): EscrowResource
    {
        $escrow = $this->managementService->cancelEscrow($escrow, $request->get('cancellation_note'));
        return new EscrowResource($escrow);
    }

    public function refund(Escrow $escrow, RefundEscrowRequest $request): EscrowResource
    {
        $escrow = $this->managementService->refundEscrow(
            $escrow,
            $request->get('amount'),
            $request->get('method'),
            $request->get('refund_reason'),
        );
        return new EscrowResource($escrow);
    }
}
