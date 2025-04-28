<?php

namespace App\Enums\Products;

use App\Traits\Helpers\EnumHelper;

enum ProductType: string
{
    use EnumHelper;

    case CONTENT = 'Content';
    case SOCIAL_MEDIA_ACCOUNT = 'Social Media Account';
    case SAAS = 'SaaS';
    case DOMAIN = 'Domain';
    case GAMING_ACCOUNT = 'Gaming Account';
}
