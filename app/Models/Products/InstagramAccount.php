<?php

namespace App\Models\Products;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class InstagramAccount extends Model
{
    protected $fillable = [
        'url',
        'business_locations',
        'business_age',
        'followers_count',
        'posts_count',
        'average_likes',
        'analytics_screenshot',
        'listing_images',
    ];

    protected $casts = [
        'business_locations' => 'array',
        'listing_images' => 'array',
    ];

    public function product(): MorphOne
    {
        return $this->morphOne(Product::class, 'productable');
    }
}
