<?php

namespace App\Http\Requests\V1\Products\SocialMedia;

use App\Enums\Products\SocialMediaType;

class InstagramAccountRequest extends SocialAccountRequest
{
    protected string $mediaType = SocialMediaType::INSTAGRAM->value;
}
