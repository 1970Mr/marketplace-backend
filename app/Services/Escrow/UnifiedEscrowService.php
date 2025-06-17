<?php

namespace App\Services\Escrow;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class UnifiedEscrowService
{
    public function getMyEscrows(User $user, Request $request): LengthAwarePaginator
    {
        return $user->escrows()
            ->with(['offer.product', 'buyer', 'seller', 'admin', 'adminEscrow', 'directEscrow', 'directChat', 'buyerChat', 'sellerChat'])
            ->filterByProductTitle($request->get('search'))
            ->filterByRelationColumn('status', $request->get('status'))
            ->filterByRelationColumn('phase', $request->get('phase'))
            ->filterByRelationColumn('stage', $request->get('stage'))
            ->latest()
            ->paginate($request->get('per_page', 10));
    }
}
