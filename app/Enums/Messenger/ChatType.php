<?php

namespace App\Enums\Messenger;

use App\Traits\Helpers\EnumHelper;

Enum ChatType: int
{
    use EnumHelper;

    case NORMAL = 1;
    case ESCROW = 2;
}
