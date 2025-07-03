<?php

namespace App\Services\Products;

use App\Http\Resources\V1\Products\SocialMedia\InstagramAccountResource;
use App\Http\Resources\V1\Products\SocialMedia\TiktokAccountResource;
use App\Http\Resources\V1\Products\SocialMedia\YoutubeChannelResource;
use App\Models\Products\InstagramAccount;
use App\Models\Products\Product;
use App\Models\Products\TiktokAccount;
use App\Models\Products\YoutubeChannel;
use App\Services\Products\Filters\CoreFilter;
use App\Services\Products\Filters\InstagramFilter;
use App\Services\Products\Filters\YoutubeFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class ProductService
{
    private array $platformFilters = [
        CoreFilter::class,
        YoutubeFilter::class,
        InstagramFilter::class,
    ];

    public function getFilteredProducts(Request $request): LengthAwarePaginator
    {
        $query = Product::published()->with(['productable', 'watchers']);

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

    public function getProductDetailsResource(Product $product): JsonResource
    {
        $productable = $product->productable()->with(['product'])->first();

        return match (true) {
            $productable instanceof YoutubeChannel => YoutubeChannelResource::make($productable),
            $productable instanceof InstagramAccount => InstagramAccountResource::make($productable),
            $productable instanceof TiktokAccount => TiktokAccountResource::make($productable),
            default => ValidationException::withMessages([
                'product_type' => 'Product type is not valid'
            ]),
        };
    }
}
