<?php

namespace App\Services\Products\Filters;

use App\Services\Products\Filters\Abstracts\SocialMediaFilter;

class InstagramFilter extends SocialMediaFilter
{
    protected string $platformType = 'instagram';
    protected string $countField = 'followers_count';
    protected string $subscriberCountField = 'followers_count';
}
