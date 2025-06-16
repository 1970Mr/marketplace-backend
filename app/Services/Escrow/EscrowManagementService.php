<?php

namespace App\Services\Escrow;

use App\Enums\Escrow\EscrowPhase;
use App\Enums\Escrow\EscrowStage;
use App\Enums\Escrow\EscrowStatus;
use App\Enums\Escrow\EscrowType;
use App\Jobs\ExpireEscrowJob;
use App\Models\Admin;
use App\Models\AdminEscrow;
use App\Models\Escrow;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class EscrowManagementService
{
    public function getUnassignedEscrows(Request $request): LengthAwarePaginator
    {
        return Escrow::with(['offer.product', 'buyer', 'seller', 'admin', 'timeSlots', 'adminEscrow'])
            ->where('type', EscrowType::ADMIN->value)
            ->whereNull('admin_id')
            ->filterByProductTitle($request->get('search'))
            ->filterByDateRange($request->get('from_date'), $request->get('to_date'))
            ->latest()
            ->paginate($request->get('per_page', 10));
    }

    public function getMyEscrows(User|Admin $user, Request $request): LengthAwarePaginator
    {
        return $user->escrows()
            ->where('type', EscrowType::ADMIN->value)
            ->with(['offer.product', 'buyer', 'seller', 'admin', 'timeSlots', 'adminEscrow'])
            ->filterByProductTitle($request->get('search'))
            ->filterByDateRange($request->get('from_date'), $request->get('to_date'))
            ->hasUnreadMessages((bool)$request->get('need_response', false))
            ->inDeliveryWithTimeSlot((bool)$request->get('in_delivery', false))
            ->latest()
            ->paginate($request->get('per_page', 10));
    }

    public function createEscrow(array $data): Escrow
    {
        $escrow = Escrow::create([
            'offer_id' => $data['offer_id'],
            'buyer_id' => $data['buyer_id'],
            'seller_id' => $data['seller_id'],
            'type' => EscrowType::ADMIN,
        ]);

        AdminEscrow::create([
            'escrow_id' => $escrow->id,
            'status' => EscrowStatus::PENDING,
            'phase' => EscrowPhase::SIGNATURE,
            'stage' => EscrowStage::AWAITING_SIGNATURE,
        ]);

        ExpireEscrowJob::dispatch($escrow)->delay(Carbon::now()->addDays(10));

        return $escrow->load(['offer.product', 'buyer', 'seller', 'admin', 'timeSlots', 'adminEscrow']);
    }

    public function assignAgent(Escrow $escrow, int $adminId): Escrow
    {
        $escrow->update(['admin_id' => $adminId]);

        $escrow->adminEscrow->update([
            'status' => EscrowStatus::ACTIVE,
            'phase' => EscrowPhase::SIGNATURE,
            'stage' => EscrowStage::AWAITING_SIGNATURE,
        ]);

        return $escrow->load(['offer.product', 'buyer', 'seller', 'admin', 'timeSlots', 'adminEscrow']);
    }

    public function completeEscrow(Escrow $escrow, float $amount, int $method): Escrow
    {
        $escrow->update([
            'amount_released' => $amount,
            'amount_released_method' => $method,
        ]);

        $escrow->adminEscrow->update([
            'stage' => EscrowStage::PAYOUT_COMPLETED,
            'phase' => EscrowPhase::COMPLETED,
            'status' => EscrowStatus::COMPLETED,
        ]);

        $escrow->offer->product->update(['is_sold' => true]);

        return $escrow->load(['offer.product', 'buyer', 'seller', 'admin', 'timeSlots', 'adminEscrow']);
    }

    public function cancelEscrow(Escrow $escrow, string $cancellationNote): Escrow
    {
        $escrow->update(['cancellation_note' => $cancellationNote]);

        $escrow->adminEscrow->update(['status' => EscrowStatus::CANCELLED]);

        return $escrow->load(['offer.product', 'buyer', 'seller', 'admin', 'timeSlots', 'adminEscrow']);
    }

    public function refundEscrow(Escrow $escrow, float $amount, int $method, string $refundReason): Escrow
    {
        $escrow->update([
            'amount_refunded' => $amount,
            'amount_refunded_method' => $method,
            'refund_reason' => $refundReason,
        ]);

        $escrow->adminEscrow->update(['status' => EscrowStatus::REFUNDED]);

        return $escrow->load(['offer.product', 'buyer', 'seller', 'admin', 'timeSlots', 'adminEscrow']);
    }
}
