<?php

namespace App\Enums\Offers;

use App\Traits\Helpers\EnumHelper;

enum OfferType: int
{
    use EnumHelper;

    case PENDING = 1;
    case ACCEPTED = 2;
    case REJECTED = 3;
}
