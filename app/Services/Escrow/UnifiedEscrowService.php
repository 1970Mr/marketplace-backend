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
            ->latest()
            ->paginate($request->get('per_page', 10));
    }
}
