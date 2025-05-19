<?php

namespace App\Enums\Escrow;

use App\Traits\Helpers\EnumHelper;

enum EscrowPhase: int
{
    use EnumHelper;

    case SIGNATURE = 1;
    case PAYMENT = 2;
    case SCHEDULING = 3;
    case DELIVERY = 4;
    case PAYOUT = 5;
}
