<?php

namespace App\Enums\Escrow;

use App\Traits\Helpers\EnumHelper;

enum DisputeResolution: int
{
    use EnumHelper;

    case BUYER_WINS = 1;
    case SELLER_WINS = 2;
}
