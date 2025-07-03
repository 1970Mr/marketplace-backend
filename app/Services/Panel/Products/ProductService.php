<?php

namespace App\Services\Panel\Products;

use App\Models\Products\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

readonly class ProductService
{
    public function getFilteredProducts(Request $request): LengthAwarePaginator
    {
        $query = Product::with('productable')
            ->where('is_completed', true)
            ->whereUserId(Auth::id());

        $this->applySearch($query, $request);
        $this->applyFilters($query, $request);

        return $query->latest()
            ->paginate($request->input('per_page', 10));
    }

    public function getDraftProducts(Request $request): LengthAwarePaginator
    {
        $query = Product::with('productable')
            ->draft()
            ->whereUserId(Auth::id());

        $this->applySearch($query, $request);

        return $query->latest()
            ->paginate($request->input('per_page', 10));
    }

    private function applySearch(Builder $query, Request $request): void
    {
        $query->when($request->filled('search'), function (Builder $q) use ($request) {
            $q->whereLike('title', "%$request->search%");
        });
    }

    private function applyFilters(Builder $query, Request $request): void
    {
        $query->when($request->filled('type'),
            fn($q) => $q->where('type', $request->type)
        );
    }

    public function deleteProduct(Product $product): void
    {
        $product->canDelete();
        abort_if($product->user_id !== Auth::id(), 401);
        $product->delete();
    }
}
