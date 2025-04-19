<?php

namespace App\Models\Products;

use Illuminate\Database\Eloquent\Model;

class YoutubeChannel extends Model
{
    protected $fillable = [
        'uuid',
        'user_id',
        'url',
        'category',
        'sub_category',
        'business_location',
        'age_of_channel',
        'subscribers',
        'monthly_revenue',
        'monthly_views',
        'monetization_method',
        'price',
        'summary',
        'about_channel',
        'analytics_screenshot',
        'listing_images',
        'allow_buyer_messages',
        'is_private',
        'is_verified',
    ];

    protected $casts = [
        'business_location' => 'array',
        'listing_images' => 'array',
        'allow_buyer_messages' => 'boolean',
        'is_private' => 'boolean',
        'is_verified' => 'boolean',
        'price' => 'decimal:2',
    ];
}
