<?php

namespace App\Services\Products;

use App\Models\Products\Product;
use App\Services\Products\Filters\CoreFilter;
use App\Services\Products\Filters\InstagramFilter;
use App\Services\Products\Filters\YoutubeFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductService
{
    private array $platformFilters = [
        CoreFilter::class,
        YoutubeFilter::class,
        InstagramFilter::class,
    ];

    public function getFilteredProducts(Request $request): LengthAwarePaginator
    {
        $query = Product::published()->with('productable');

        $this->applySearch($query, $request);
        $this->applyAllFilters($query, $request);
        $this->applySorting($query, $request);

        return $query->paginate($request->input('per_page', 10));
    }

    private function applyAllFilters(Builder $query, Request $request): void
    {
        foreach ($this->platformFilters as $filterClass) {
            app($filterClass)->apply($query, $request);
        }
    }

    private function applySearch(Builder $query, Request $request): void
    {
        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'LIKE', "%{$request->search}%")
                    ->orWhere('summary', 'LIKE', "%{$request->search}%")
                    ->orWhere('about_business', 'LIKE', "%{$request->search}%");
            });
        }
    }

    private function applySorting(Builder $query, Request $request): void
    {
        $sortField = $this->validateSortField($request->input('sort_by', 'created_at'));
        $sortOrder = $request->input('sort_order', 'desc');

        $query->orderBy($sortField, $sortOrder);
    }

    private function validateSortField(string $field): string
    {
        return in_array($field, ['created_at', 'price']) ? $field : 'created_at';
    }
}
