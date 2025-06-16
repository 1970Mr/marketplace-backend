<?php

namespace App\Enums\Escrow;

use App\Traits\Helpers\EnumHelper;

enum DirectEscrowStatus: int
{
    use EnumHelper;

    case PENDING = 1;
    case ACTIVE = 2;
    case COMPLETED = 3;
    case CANCELLED = 4;
    case REFUNDED = 5;
    case DISPUTED = 6;
    case RESOLVED = 7;
}
