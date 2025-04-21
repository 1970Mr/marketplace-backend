<?php

namespace App\Enums\Products;

use App\Traits\Helpers\EnumHelper;

Enum SocialMediaType: string
{
    use EnumHelper;

    case YOUTUBE = 'Youtube';
    case INSTAGRAM = 'Instagram';
    case TIKTOK = 'TikTok';
}
