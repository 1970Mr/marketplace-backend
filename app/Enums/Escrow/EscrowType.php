<?php

namespace App\Enums\Escrow;

use App\Traits\Helpers\EnumHelper;

enum EscrowType: int
{
    use EnumHelper;

    case ADMIN = 1;
    case DIRECT = 2;
}
