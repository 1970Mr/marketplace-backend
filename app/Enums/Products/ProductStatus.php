<?php

namespace App\Enums\Products;

use App\Traits\Helpers\EnumHelper;

enum ProductStatus: int
{
    use EnumHelper;

    case PENDING = 1;
    case APPROVED = 2;
    case DISAPPROVED = 3;
}
