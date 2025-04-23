<?php

namespace App\Services\Products\Filters\Abstracts;

use App\Services\Products\Filters\Contracts\PlatformFilter;
use App\Services\Products\Filters\traits\RangeFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

abstract class SocialMediaFilter implements PlatformFilter
{
    use RangeFilter;

    protected string $platformType;
    protected string $countField;
    protected string $subscriberCountField;

    public function apply(Builder $query, Request $request): void
    {
        $this->applySubscriberCountFilter($query, $request);
        $this->applyBusinessLocationFilter($query, $request);
        $this->applyBusinessAgeFilter($query, $request);
    }

    private function applySubscriberCountFilter(Builder $query, Request $request): void
    {
        $this->applyRangeFilter(
            $query,
            $request,
            "min_{$this->countField}",
            "max_{$this->countField}",
            $this->subscriberCountField,
            'productable'
        );
    }

    private function applyBusinessLocationFilter(Builder $query, Request $request): void
    {
        $query->when($request->has('business_locations'), function ($q) use ($request) {
//            $q->whereHas('productable', fn($sub) => $sub->whereJsonContains('business_locations', $request->business_locations));
            $q->whereHas('productable', function ($sub) use ($request) {
                $sub->where(function ($inner) use ($request) {
                    foreach ($request->business_locations as $location) {
                        $inner->orWhereJsonContains('business_locations', $location);
                    }
                });
            });
        });
    }

    private function applyBusinessAgeFilter(Builder $query, Request $request): void
    {
        $query->when($request->has('business_age'), function ($q) use ($request) {
            $ageInMonths = $request->business_age * 12;
            $q->whereHas('productable', fn($sub) => $sub->where('business_age', '<=', $ageInMonths));
        });
    }
}
