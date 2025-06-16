<?php

namespace App\Enums\Escrow;

use App\Traits\Helpers\EnumHelper;

enum DirectEscrowStage: int
{
    use EnumHelper;

    // Phase 1: Signature
    case AWAITING_SIGNATURE = 1;
    case AWAITING_SELLER_SIGNATURE = 2;
    case AWAITING_BUYER_SIGNATURE = 3;

    // Phase 2: Payment
    case AWAITING_PAYMENT = 4;
    case PAYMENT_UPLOADED = 5;

    // Phase 3: Delivery
    case AWAITING_DELIVERY = 6;

    // Phase 4: Confirmation
    case AWAITING_BUYER_CONFIRMATION = 7;

    // Phase 5: Dispute
    case DISPUTE_UNDER_REVIEW = 8;
    case DISPUTE_RESOLVED = 9;

    // Phase 6: Payout
    case AWAITING_PAYOUT = 10;
    case PAYOUT_COMPLETED = 11;
}
