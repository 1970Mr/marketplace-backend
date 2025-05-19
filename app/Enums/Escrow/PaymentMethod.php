<?php

namespace App\Enums\Escrow;

use App\Traits\Helpers\EnumHelper;

enum PaymentMethod: int
{
    use EnumHelper;

    case RBC = 1;
    case CHASE = 2;
    case WISE = 3;
}
