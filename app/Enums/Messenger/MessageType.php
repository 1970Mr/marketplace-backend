<?php

namespace App\Enums\Messenger;

use App\Traits\Helpers\EnumHelper;

enum MessageType: int
{
    use EnumHelper;

    case TEXT = 1;
    case OFFER = 2;
    case SYSTEM = 3;
}
