<?php

namespace App\Enums\Escrow;

use App\Traits\Helpers\EnumHelper;

enum EscrowStatus: int
{
    use EnumHelper;

    case PENDING = 1;
    case ACTIVE = 2;
    case COMPLETED = 3;
    case CANCELLED = 4;
    case REFUNDED = 5;
    case EXPIRED = 6;
}
