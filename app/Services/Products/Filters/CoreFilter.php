<?php

namespace App\Services\Products\Filters;

use App\Services\Products\Filters\Contracts\PlatformFilter;
use App\Services\Products\Filters\traits\RangeFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CoreFilter implements PlatformFilter
{
    use RangeFilter;

    public function apply(Builder $query, Request $request): void
    {
        $this->applyProductTypeFilter($query, $request);
        $this->applyIndustryFilter($query, $request);
        $this->applyPriceFilters($query, $request);
    }

    private function applyProductTypeFilter(Builder $query, Request $request): void
    {
        $query->when($request->filled('product_types'),
            fn($q) => $q->whereIn('type', $request->product_types)
        );
    }

    private function applyIndustryFilter(Builder $query, Request $request): void
    {
        $query->when($request->filled('industries'),
            fn($q) => $q->whereIn('industry', $request->industries)
        );
    }

    private function applyPriceFilters(Builder $query, Request $request): void
    {
        $this->applyRangeFilter(
            $query,
            $request,
            'min_price',
            'max_price',
            'price'
        );
    }
}
