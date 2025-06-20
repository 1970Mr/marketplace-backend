<?php

namespace App\Services\DirectEscrow;

use App\Enums\Escrow\DirectEscrowPhase;
use App\Enums\Escrow\DirectEscrowStage;
use App\Enums\Escrow\DirectEscrowStatus;
use App\Enums\Escrow\EscrowType;
use App\Models\Admin;
use App\Models\DirectEscrow;
use App\Models\Escrow;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class DirectEscrowManagementService
{
    public function getMyDirectEscrows(User|Admin $user, Request $request): LengthAwarePaginator
    {
        return $user->escrows()
            ->where('type', EscrowType::DIRECT->value)
            ->with(['offer.product', 'buyer', 'seller', 'directEscrow'])
            ->filterByProductTitle($request->get('search'))
            ->filterByRelationColumn('status', $request->get('status'))
            ->filterByRelationColumn('phase', $request->get('phase'))
            ->filterByRelationColumn('stage', $request->get('stage'))
            ->filterByDateRange($request->get('from_date'), $request->get('to_date'))
            ->hasUnreadMessages((bool)$request->get('need_response', false))
            ->latest()
            ->paginate($request->get('per_page', 10));
    }

    public function createDirectEscrow(array $data): Escrow
    {
        $escrow = Escrow::create([
            'offer_id' => $data['offer_id'],
            'buyer_id' => $data['buyer_id'],
            'seller_id' => $data['seller_id'],
            'type' => EscrowType::DIRECT,
        ]);

        DirectEscrow::create([
            'escrow_id' => $escrow->id,
            'status' => DirectEscrowStatus::PENDING,
            'phase' => DirectEscrowPhase::SIGNATURE,
            'stage' => DirectEscrowStage::AWAITING_SIGNATURE,
        ]);

        return $escrow->load(['offer.product', 'buyer', 'seller', 'directEscrow']);
    }

    public function getAllDirectEscrows(Request $request): LengthAwarePaginator
    {
        return Escrow::with(['offer.product', 'buyer', 'seller', 'directEscrow'])
            ->where('type', EscrowType::DIRECT->value)
            ->filterByProductTitle($request->get('search'))
            ->filterByDateRange($request->get('from_date'), $request->get('to_date'))
            ->latest()
            ->paginate($request->get('per_page', 10));
    }

    public function getUnassignedDirectEscrows(Request $request): LengthAwarePaginator
    {
        return Escrow::with(['offer.product', 'buyer', 'seller', 'directEscrow'])
            ->where('type', EscrowType::DIRECT->value)
            ->whereNull('admin_id')
            ->filterByProductTitle($request->get('search'))
            ->filterByDateRange($request->get('from_date'), $request->get('to_date'))
            ->latest()
            ->paginate($request->get('per_page', 10));
    }

    public function assignAgent(Escrow $escrow, int $adminId): Escrow
    {
        $escrow->update(['admin_id' => $adminId]);

        $escrow->directEscrow->update([
            'status' => DirectEscrowStatus::ACTIVE,
        ]);

        return $escrow->load(['offer.product', 'buyer', 'seller', 'directEscrow']);
    }

    public function completeEscrow(Escrow $escrow, float $amount, int $method): Escrow
    {
        $escrow->update([
            'amount_released' => $amount,
            'amount_released_method' => $method,
        ]);

        $escrow->directEscrow->update([
            'status' => DirectEscrowStatus::COMPLETED,
            'phase' => DirectEscrowPhase::COMPLETED,
            'stage' => DirectEscrowStage::PAYOUT_COMPLETED,
        ]);

        $escrow->offer->product->update(['is_sold' => true]);

        return $escrow->load(['offer.product', 'buyer', 'seller', 'directEscrow']);
    }

    public function refundEscrow(Escrow $escrow): Escrow
    {
        $escrow->update([
            'amount_refunded' => $escrow->amount_received,
            'amount_refunded_method' => $escrow->amount_received_method,
        ]);

        $escrow->directEscrow->update([
            'status' => DirectEscrowStatus::REFUNDED,
            'phase' => DirectEscrowPhase::COMPLETED,
        ]);

        return $escrow->load(['offer.product', 'buyer', 'seller', 'directEscrow']);
    }
}
