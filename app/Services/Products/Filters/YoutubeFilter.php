<?php

namespace App\Services\Products\Filters;

use App\Services\Products\Filters\Abstracts\SocialMediaFilter;

class YoutubeFilter extends SocialMediaFilter
{
    protected string $platformType = 'youtube';
    protected string $countField = 'subscribers_count';
    protected string $subscriberCountField = 'subscribers_count';
}
