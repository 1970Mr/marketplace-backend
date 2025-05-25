<?php

namespace App\Enums\Escrow;

use App\Traits\Helpers\EnumHelper;

enum EscrowStage: int
{
    use EnumHelper;

    // Phase 0: Signature
    case AWAITING_SIGNATURE = 1;
    case AWAITING_SELLER_SIGNATURE = 2;
    case AWAITING_BUYER_SIGNATURE = 3;

    // Phase 1: Payment
    case AWAITING_PAYMENT = 4;
    case PAYMENT_UPLOADED = 5;

    // Phase 2: Scheduling
    case AWAITING_SCHEDULING = 6;
    case SCHEDULING_SUGGESTED = 7;
    case SCHEDULING_REJECTED = 8;

    // Phase 3: Delivery
    case DELIVERY_PENDING = 9;

    // Phase 4: Payout
    case AWAITING_PAYOUT = 10;
    case PAYOUT_COMPLETED = 11;
}
