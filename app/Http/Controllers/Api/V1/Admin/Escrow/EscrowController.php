<?php

namespace App\Http\Controllers\Api\V1\Admin\Escrow;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Escrow\{
    CancelEscrowRequest,
    ConfirmPaymentRequest,
    RefundEscrowRequest,
    ReleaseFundsRequest
};
use App\Http\Resources\V1\Escrow\EscrowResource;
use App\Models\Escrow;
use App\Services\Escrow\DeliveryService;
use App\Services\Escrow\EscrowManagementService;
use App\Services\Escrow\PaymentService;
use App\Services\Escrow\PayoutService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Auth;

class EscrowController extends Controller
{
    public function __construct(
        readonly private EscrowManagementService $managementService,
        readonly private PaymentService $paymentService,
        readonly private DeliveryService $deliveryService,
        readonly private PayoutService $payoutService
    ) {}

    public function getMyEscrows(Request $request): ResourceCollection
    {
        $escrows = $this->managementService->getMyEscrows(Auth::guard('admin-api')->user(), $request);
        return EscrowResource::collection($escrows);
    }

    public function show(Escrow $escrow): EscrowResource
    {
        $escrow->load(['offer', 'buyer', 'seller', 'admin']);
        return new EscrowResource($escrow);
    }

    public function accept(Escrow $escrow): EscrowResource
    {
        $adminId = Auth::guard('admin-api')->id();
        $escrow = $this->managementService->acceptEscrow($escrow, $adminId);
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

    public function releaseFunds(ReleaseFundsRequest $request, Escrow $escrow): EscrowResource
    {
        $escrow = $this->payoutService->releaseFunds(
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
        $escrow = $this->payoutService->refundEscrow(
            $escrow,
            $request->get('amount'),
            $request->get('method'),
            $request->get('refund_reason'),
        );
        return new EscrowResource($escrow);
    }
}
