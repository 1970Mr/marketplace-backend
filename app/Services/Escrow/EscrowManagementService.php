<?php

namespace App\Services\Escrow;

use App\Enums\Escrow\EscrowPhase;
use App\Enums\Escrow\EscrowStage;
use App\Enums\Escrow\EscrowStatus;
use App\Jobs\ExpireEscrowJob;
use App\Models\Admin;
use App\Models\Escrow;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class EscrowManagementService
{
    public function getUnassignedEscrows(Request $request): LengthAwarePaginator
    {
        return Escrow::with(['offer.product', 'buyer', 'seller', 'admin'])
            ->whereNull('admin_id')
            ->filterByProductTitle($request->get('search'))
            ->filterByDateRange($request->get('from_date'), $request->get('to_date'))
            ->paginate($request->get('per_page', 10));
    }

    public function getMyEscrows(User|Admin $user, Request $request): LengthAwarePaginator
    {
        return $user->escrows()
            ->with(['offer.product', 'buyer', 'seller', 'admin'])
            ->filterByProductTitle($request->get('search'))
            ->filterBy('status', $request->get('status'))
            ->filterBy('phase', $request->get('phase'))
            ->filterBy('stage', $request->get('stage'))
            ->paginate($request->get('per_page', 10));
    }

    public function createEscrow(array $data): Escrow
    {
        $escrow = Escrow::create([
            'offer_id' => $data['offer_id'],
            'buyer_id' => $data['buyer_id'],
            'seller_id' => $data['seller_id'],
            'status' => EscrowStatus::PENDING,
        ]);

        ExpireEscrowJob::dispatch($escrow)->delay(Carbon::now()->addDays(10));

        return $escrow;
    }

    public function assignAgent(Escrow $escrow, int $adminId): Escrow
    {
        $escrow->admin_id = $adminId;
        $escrow->status = EscrowStatus::ACTIVE;
        $escrow->phase = EscrowPhase::SIGNATURE;
        $escrow->stage = EscrowStage::AWAITING_SIGNATURE;
        $escrow->save();

        return $escrow;
    }

    public function cancelEscrow(Escrow $escrow, string $cancellationNote): Escrow
    {
        $escrow->cancellation_note = $cancellationNote;
        $escrow->status = EscrowStatus::CANCELLED;
        $escrow->save();

        return $escrow;
    }
}
