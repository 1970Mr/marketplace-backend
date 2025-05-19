<?php

namespace App\Enums\Escrow;

use App\Traits\Helpers\EnumHelper;

enum Weekday: int
{
    use EnumHelper;

    case MONDAY = 1;
    case TUESDAY = 2;
    case WEDNESDAY = 3;
    case THURSDAY = 4;
    case FRIDAY = 5;
}
