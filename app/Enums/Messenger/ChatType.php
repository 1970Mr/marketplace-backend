<?php

namespace App\Enums\Messenger;

use App\Traits\Helpers\EnumHelper;

enum ChatType: int
{
    use EnumHelper;

    case USER_TO_USER = 1;
    case ESCROW_SELLER = 2;
    case ESCROW_BUYER = 3;
    case DIRECT_ESCROW = 4;
}
