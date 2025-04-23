<?php

namespace App\Models\Products;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class SocialAccount extends Model
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

    protected $appends = ['engagement_rate'];

    protected function engagementRate(): Attribute
    {
        return Attribute::make(
            get: function () {
                $engagementRate = 0;
                if ($this->followers_count > 0 && $this->average_likes !== null) {
                    $engagementRate = round(($this->average_likes / $this->followers_count) * 100, 2);
                }
                return $engagementRate;
            },
        );
    }

    public function product(): MorphOne
    {
        return $this->morphOne(Product::class, 'productable');
    }
}
