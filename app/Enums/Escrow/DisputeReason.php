<?php

namespace App\Enums\Escrow;

use App\Traits\Helpers\EnumHelper;

enum DisputeReason: int
{
    use EnumHelper;

    case INCOMPLETE_DELIVERY = 1;
    case INCORRECT_ASSET_DELIVERED = 2;
    case ACCESS_OR_CREDENTIALS_NOT_PROVIDED = 3;
    case ASSET_QUALITY_ISSUE = 4;
    case OTHER = 5;
}
