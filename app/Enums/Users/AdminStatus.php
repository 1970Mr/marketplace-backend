<?php

namespace App\Enums\Users;

use App\Traits\Helpers\EnumHelper;

enum AdminStatus: int
{
    use EnumHelper;

    case ACTIVE = 1;
    case INACTIVE = 2;
}
