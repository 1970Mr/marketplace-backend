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
        $query = Product::query()->with(['user', 'productable'])->withTrashed();

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $query->where(function (Builder $q) use ($filters) {
                $q->where('title', 'like', "%{$filters['search']}%")
                    ->orWhereHas('user', function (Builder $q) use ($filters) {
                        $q->where('name', 'like', "%{$filters['search']}%");
                    });
            });
        }

        if (isset($filters['only_trashed'])) {
            $query->onlyTrashed();
        }

        if (!empty($filters['from_date'])) {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (!empty($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        $page = $filters['page'] ?? 1;
        $perPage = $filters['per_page'] ?? 10;

        return $query->latest()->paginate($perPage, ['*'], 'page', $page);
    }

    public function getProductDetails(Product $product): array
    {
        $productsCount = Product::where('user_id', $product->user_id)
            ->whereNull('deleted_at')
            ->count();

        return [
            'data' => new ProductResource($product->load(['user', 'productable'])),
            'meta' => [
                'total_listings' => $productsCount,
            ]
        ];
    }
}
