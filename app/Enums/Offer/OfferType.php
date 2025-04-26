<?php

namespace App\Enums\Offer;

use App\Traits\Helpers\EnumHelper;

Enum OfferType: int
{
    use EnumHelper;

    case PENDING = 1;
    case ACCEPTED = 2;
    case REJECTED = 3;
}
