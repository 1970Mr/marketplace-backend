<?php

namespace App\Http\Requests\V1\Products\SocialMedia;

use App\Enums\Products\SocialMediaType;

class TiktokAccountRequest extends SocialAccountRequest
{
    protected string $mediaType = SocialMediaType::TIKTOK->value;
}
