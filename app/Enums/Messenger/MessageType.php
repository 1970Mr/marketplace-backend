<?php

namespace App\Enums\Messenger;

use App\Traits\Helpers\EnumHelper;

Enum MessageType: int
{
    use EnumHelper;

    case TEXT = 1;
    case OFFER = 2;
}
