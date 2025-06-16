<?php

namespace App\Enums\Escrow;

use App\Traits\Helpers\EnumHelper;

enum DirectEscrowPhase: int
{
    use EnumHelper;

    case SIGNATURE = 1;
    case PAYMENT = 2;
    case DELIVERY = 3;
    case CONFIRMATION = 4;
    case DISPUTE = 5;
    case PAYOUT = 6;
    case COMPLETED = 7;
}
