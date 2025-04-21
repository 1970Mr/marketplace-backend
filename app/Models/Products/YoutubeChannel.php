<?php

namespace App\Models\Products;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class YoutubeChannel extends Model
{
    protected $fillable = [
        'url',
        'business_locations',
        'channel_age',
        'subscribers',
        'monthly_revenue',
        'monthly_views',
        'monetization_method',
        'analytics_screenshot',
        'listing_images',
    ];

    protected $casts = [
        'business_locations' => 'array',
        'listing_images' => 'array',
        'monthly_revenue' => 'decimal:2',
    ];

    public function product(): MorphOne
    {
        return $this->morphOne(Product::class, 'productable');
    }
}
