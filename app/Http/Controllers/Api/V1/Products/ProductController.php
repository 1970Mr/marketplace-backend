<?php

namespace App\Http\Controllers\Api\V1\Products;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Products\ProductResource;
use App\Models\Products\Product;
use App\Models\Products\YoutubeChannel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::published()->with('productable');

        $this->applySearch($query, $request);
        $this->applyFilters($query, $request);
        $this->applySorting($query, $request);

        $products = $query->paginate($request->input('per_page', 10));

        return response()->json([
            'data' => ProductResource::collection($products),
            'meta' => $this->getMetaData($products),
        ]);
    }

    private function applySearch($query, Request $request): void
    {
        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'LIKE', "%{$request->search}%")
                    ->orWhere('summary', 'LIKE', "%{$request->search}%")
                    ->orWhere('about_business', 'LIKE', "%{$request->search}%");
            });
        }
    }

    private function applyFilters($query, Request $request): void
    {
        $query->when($request->has('product_types'), function ($q) use ($request) {
            $q->whereIn('type', $request->product_types);
        });

        $query->when($request->has('industries'), function ($q) use ($request) {
            $q->whereIn('industry', $request->industries);
        });

        $query->when($request->has('min_price'), function ($q) use ($request) {
            $q->where('price', '>=', $request->min_price);
        });

        $query->when($request->has('max_price'), function ($q) use ($request) {
            $q->where('price', '<=', $request->max_price);
        });

        $query->when($request->has(['min_price', 'max_price']),
            function ($q) use ($request) {
                $q->where('price', '>=', $request->min_price)
                    ->where('price', '<=', $request->max_price);
            },
            function ($q) use ($request) {
                if ($request->has('min_price')) {
                    $q->where('price', '>=', $request->min_price);
                }
                if ($request->has('max_price')) {
                    $q->where('price', '<=', $request->max_price);
                }
            }
        );

        $this->applyYoutubeFilters($query, $request);
        $this->applyInstagramFilters($query, $request);
    }

    private function applyYoutubeFilters(Builder $query, Request $request): void
    {
        $query->when($request->has(['min_revenue', 'max_revenue']),
            function (Builder $q) use ($request) {
                $q->whereHas('productable', function ($subQuery) use ($request) {
                    $subQuery->where('monthly_revenue', '>=', $request->min_revenue)
                        ->where('monthly_revenue', '<=', $request->max_revenue);
                });
            },
            function (Builder $q) use ($request) {
                if ($request->has('min_revenue')) {
                    $q->whereHas('productable', function (Builder $subQuery) use ($request) {
                        $subQuery->where('monthly_revenue', '>=', $request->min_revenue);
                    });
                }
                if ($request->has('max_revenue')) {
                    $q->whereHas('productable', function (Builder $subQuery) use ($request) {
                        $subQuery->where('monthly_revenue', '<=', $request->max_revenue);
                    });
                }
            }
        );

        if ($request->has('business_locations')) {
            $query->whereHas('productable', function (Builder $subQuery) use ($request) {
                $subQuery->whereJsonContains('business_locations', $request->business_locations);
            });
        }

        if ($request->has('business_age')) {
            $query->whereHas('productable', function (Builder $subQuery) use ($request) {
                $businessAgeByMonth = $request->business_age * 12;
                $subQuery->where('channel_age', '<=', $businessAgeByMonth);
            });
        }
    }

    private function applyInstagramFilters($query, Request $request): void
    {
        $query->when($request->has('instagram_type'), function ($q) use ($request) {
            $q->whereHas('productable', function ($subQuery) use ($request) {
                $subQuery->where('instagram_type', $request->instagram_type);
            });
        });
    }

    private function applySorting($query, Request $request): void
    {
        $sortField = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');

        $allowedSortFields = ['created_at', 'price'];

        if (!in_array($sortField, $allowedSortFields, true)) {
            $sortField = 'created_at';
        }

        $query->orderBy($sortField, $sortOrder);
    }

    private function getMetaData(LengthAwarePaginator $products): array
    {
        return [
            'current_page' => $products->currentPage(),
            'last_page' => $products->lastPage(),
            'per_page' => $products->perPage(),
            'total' => $products->total(),
            'from' => $products->firstItem(),
            'to' => $products->lastItem(),
            'has_more_pages' => $products->hasMorePages(),
        ];
    }

    public function show(Product $product): JsonResponse
    {
        $product->load('productable');

        return response()->json([
            'data' => $this->formatProductDetails($product)
        ]);
    }

    private function formatProductDetails(Product $product): array
    {
        $base = [
            'id' => $product->id,
            'uuid' => $product->uuid,
            'type' => $product->type,
            'sub_type' => $product->sub_type,
            'title' => $product->title,
            'price' => $product->price,
            'industry' => $product->industry,
            'about_business' => $product->about_business,
            'is_verified' => $product->is_verified,
            'is_sponsored' => $product->is_sponsored,
            'created_at' => $product->created_at,
        ];

        if ($product->productable instanceof YoutubeChannel) {
            $base = array_merge($base, [
                'subscribers' => $product->productable->subscribers,
                'monthly_revenue' => $product->productable->monthly_revenue,
                'channel_age' => $product->productable->channel_age,
                'analytics_screenshot' => $product->productable->analytics_screenshot,
            ]);
        }
        return $base;
    }
}
