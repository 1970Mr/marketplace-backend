<?php

namespace App\Enums\Users;

use App\Traits\Helpers\EnumHelper;

enum UserStatus: int
{
    use EnumHelper;

    case ACTIVE = 1;
    case INACTIVE = 2;
    case BANNED = 3;
}
