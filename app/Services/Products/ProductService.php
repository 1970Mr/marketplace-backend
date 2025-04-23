<?php

namespace App\Services\Products;

use App\Models\Products\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductService
{
    public function getFilteredProducts(Request $request): LengthAwarePaginator
    {
        $query = Product::published()->with('productable');

        $this->applySearch($query, $request);
        $this->applyFilters($query, $request);
        $this->applySorting($query, $request);

        return $query->paginate($request->input('per_page', 10));
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

    private function applyFilters(Builder $query, Request $request): void
    {
        $this->applyCoreFilters($query, $request);
        $this->applyYoutubeFilters($query, $request);
        $this->applyInstagramFilters($query, $request);
    }

    private function applyCoreFilters(Builder $query, Request $request): void
    {
        $this->applyProductTypeFilter($query, $request);
        $this->applyIndustryFilter($query, $request);
        $this->applyPriceFilters($query, $request);
    }

    private function applyProductTypeFilter(Builder $query, Request $request): void
    {
        $query->when($request->filled('product_types'), fn($q) => $q->whereIn('type', $request->product_types));
    }

    private function applyIndustryFilter(Builder $query, Request $request): void
    {
        $query->when($request->filled('industries'), fn($q) => $q->whereIn('industry', $request->industries));
    }

    private function applyPriceFilters(Builder $query, Request $request): void
    {
        $query->when($request->has(['min_price', 'max_price']),
            function ($q) use ($request) {
                $q->whereBetween('price', [$request->min_price, $request->max_price]);
            },
            function ($q) use ($request) {
                $this->applyMinPriceFilter($q, $request);
                $this->applyMaxPriceFilter($q, $request);
            }
        );
    }

    private function applyMinPriceFilter(Builder $query, Request $request): void
    {
        $query->when($request->has('min_price'), fn($q) => $q->where('price', '>=', $request->min_price));
    }

    private function applyMaxPriceFilter(Builder $query, Request $request): void
    {
        $query->when($request->has('max_price'), fn($q) => $q->where('price', '<=', $request->max_price));
    }

    private function applyYoutubeFilters(Builder $query, Request $request): void
    {
        $this->applyRevenueFilters($query, $request);
        $this->applyBusinessLocationFilter($query, $request);
        $this->applyBusinessAgeFilter($query, $request);
    }

    private function applyRevenueFilters(Builder $query, Request $request): void
    {
        $query->when($request->has(['min_revenue', 'max_revenue']),
            function (Builder $q) use ($request) {
                $q->whereHas('productable', function ($subQuery) use ($request) {
                    $subQuery->whereBetween('monthly_revenue', [$request->min_revenue, $request->max_revenue]);
                });
            },
            function (Builder $q) use ($request) {
                $this->applyMinRevenueFilter($q, $request);
                $this->applyMaxRevenueFilter($q, $request);
            }
        );
    }

    private function applyMinRevenueFilter(Builder $query, Request $request): void
    {
        $query->when($request->has('min_revenue'), function ($q) use ($request) {
            $q->whereHas('productable', fn($subQuery) => $subQuery->where('monthly_revenue', '>=', $request->min_revenue));
        });
    }

    private function applyMaxRevenueFilter(Builder $query, Request $request): void
    {
        $query->when($request->has('max_revenue'), function ($q) use ($request) {
            $q->whereHas('productable', fn($subQuery) => $subQuery->where('monthly_revenue', '<=', $request->max_revenue));
        });
    }

    private function applyBusinessLocationFilter(Builder $query, Request $request): void
    {
        $query->when($request->has('business_locations'), function ($q) use ($request) {
            $q->whereHas('productable', fn($subQuery) => $subQuery->whereJsonContains('business_locations', $request->business_locations));
        });
    }

    private function applyBusinessAgeFilter(Builder $query, Request $request): void
    {
        $query->when($request->has('business_age'), function ($q) use ($request) {
            $q->whereHas('productable', function (Builder $subQuery) use ($request) {
                $businessAgeByMonth = $request->business_age * 12;
                $subQuery->where('business_age', '<=', $businessAgeByMonth);
            });
        });
    }

    private function applyInstagramFilters(Builder $query, Request $request): void
    {
        $query->when($request->has('instagram_type'), function ($q) use ($request) {
            //
        });
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

    public function getMetaData(LengthAwarePaginator $paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
            'has_more_pages' => $paginator->hasMorePages(),
        ];
    }
}
