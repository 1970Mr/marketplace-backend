<?php

namespace App\Enums\Messenger;

use App\Traits\Helpers\EnumHelper;

enum ChatType: int
{
    use EnumHelper;

    case NORMAL = 1;
    case ESCROW = 2;
}
