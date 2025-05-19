<?php

namespace App\Enums\Escrow;

use App\Traits\Helpers\EnumHelper;

enum EscrowStage: int
{
    use EnumHelper;

    // Phase 0: Signature
    case AWAITING_SIGNATURE = 1;

    // Phase 1: Payment
    case AWAITING_PAYMENT = 2;
    case PAYMENT_UPLOADED = 3;

    // Phase 2: Scheduling
    case AWAITING_SCHEDULING = 4;
    case SCHEDULING_SUGGESTED = 5;
    case SCHEDULING_REJECTED = 6;

    // Phase 3: Delivery
    case DELIVERY_PENDING = 7;

    // Phase 4: Payout
    case AWAITING_PAYOUT = 8;
    case PAYOUT_COMPLETED = 9;
}
