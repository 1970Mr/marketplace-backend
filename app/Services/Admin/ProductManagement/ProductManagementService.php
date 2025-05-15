<?php

namespace App\Services\Admin\ProductManagement;

use App\Http\Resources\V1\Products\ProductResource;
use App\Models\Products\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductManagementService
{
    public function getFilteredProducts(array $filters): LengthAwarePaginator
    {
        // Base query with eager loading, include soft deleted products
        $query = Product::query()->with(['user', 'productable'])->withTrashed();

        // Apply search
        if (!empty($filters['search'])) {
            $query->where(function (Builder $q) use ($filters) {
                $q->where('industry', 'like', "%{$filters['search']}%")
                    ->orWhereHas('user', function (Builder $q) use ($filters) {
                        $q->where('name', 'like', "%{$filters['search']}%");
                    });
            });
        }

        // Apply date range filter
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        // Apply category filter
        if (!empty($filters['category'])) {
            $query->where('type', $filters['category']);
        }

        // Apply status filter
        if (isset($filters['status']) && $filters['status'] !== 'all') {
            if ($filters['status'] === 'deleted') {
                $query->onlyTrashed();
            } else {
                $query->where('status', $filters['status']);
            }
        }

        // Apply price range filter
        if (!empty($filters['price_min'])) {
            $query->where('price', '>=', $filters['price_min']);
        }

        if (!empty($filters['price_max'])) {
            $query->where('price', '<=', $filters['price_max']);
        }

        // Apply Pagination
        $page = $filters['page'] ?? 1;
        $perPage = $filters['per_page'] ?? 10;

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function getProductDetails($product_id): array
    {
        $product = Product::withTrashed()
            ->with(['user', 'productable'])
            ->findOrFail($product_id);


        // Get count of active products for the same user
        $productsCount = Product::where('user_id', $product->user_id)
            ->whereNull('deleted_at')
            ->count();

        // Prepare response data
        return [
            'product' => new ProductResource($product),
            'total_listings' => $productsCount,
        ];
    }

    public function updateProductStatus($product_id, $status): ProductResource
    {
        $product = Product::findOrFail($product_id);

        $product->update(['status' => $status]);

        return new ProductResource($product);
    }
}
