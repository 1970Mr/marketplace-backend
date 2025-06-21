<?php

namespace App\Http\Controllers\Api\V1\Admin\DirectEscrow;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\DirectEscrow\ResolveDisputeRequest;
use App\Http\Requests\V1\Escrow\CompleteEscrowRequest;
use App\Http\Requests\V1\Escrow\ConfirmPaymentRequest;
use App\Http\Resources\V1\DirectEscrow\DirectEscrowResource;
use App\Models\Admin;
use App\Models\Escrow;
use App\Services\DirectEscrow\DirectEscrowDisputeService;
use App\Services\DirectEscrow\DirectEscrowManagementService;
use App\Services\DirectEscrow\DirectEscrowPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class DirectEscrowController extends Controller
{
    public function __construct(
        readonly private DirectEscrowManagementService $managementService,
        readonly private DirectEscrowDisputeService $disputeService,
        readonly private DirectEscrowPaymentService $paymentService
    )
    {
    }

    public function index(Request $request): JsonResponse
    {
        $escrows = $this->managementService->getAllDirectEscrows($request);
        return DirectEscrowResource::collection($escrows)->response();
    }

    public function getUnassignedEscrows(Request $request): ResourceCollection
    {
        $escrows = $this->managementService->getUnassignedDirectEscrows($request);
        return DirectEscrowResource::collection($escrows);
    }

    public function getMyEscrows(Request $request): ResourceCollection
    {
        $escrows = $this->managementService->getMyDirectEscrows(auth('admin-api')->user(), $request);
        return DirectEscrowResource::collection($escrows);
    }

    public function show(Escrow $escrow): JsonResponse
    {
        $escrow->load(['offer.product', 'buyer', 'seller', 'directEscrow', 'directChat']);
        return DirectEscrowResource::make($escrow)->response();
    }

    public function assignAgent(Escrow $escrow, Admin $admin): JsonResponse
    {
        $escrow = $this->managementService->assignAgent($escrow, $admin->id);
        return DirectEscrowResource::make($escrow)->response();
    }

    public function confirmPayment(ConfirmPaymentRequest $request, Escrow $escrow): JsonResponse
    {
        $escrow = $this->paymentService->confirmPayment($escrow, $request->validated());
        return DirectEscrowResource::make($escrow)->response();
    }

    public function resolveDispute(ResolveDisputeRequest $request, Escrow $escrow): JsonResponse
    {
        $escrow = $this->disputeService->resolveDispute(
            $escrow,
            $request->validated('resolution'),
            $request->validated('note'),
            $request->validated('amount'),
            $request->validated('method')
        );
        return DirectEscrowResource::make($escrow)->response();
    }

    public function complete(CompleteEscrowRequest $request, Escrow $escrow): JsonResponse
    {
        $escrow = $this->managementService->completeEscrow(
            $escrow,
            $request->validated('amount'),
            $request->validated('method')
        );
        return DirectEscrowResource::make($escrow)->response();
    }

    public function refund(Escrow $escrow): JsonResponse
    {
        $escrow = $this->managementService->refundEscrow($escrow);
        return DirectEscrowResource::make($escrow)->response();
    }
}
