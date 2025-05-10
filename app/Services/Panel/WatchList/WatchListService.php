<?php

namespace App\Services\Panel\WatchList;

use App\Models\Products\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class WatchListService
{
    public function getWatchListedProducts(Request $request): LengthAwarePaginator
    {
        $query = Auth::user()
            ?->watchlist()
            ->with(['user', 'productable']);

        $this->applySearch($query, $request);

        return $query->latest('product_user_watchlist.created_at')
            ->paginate($request->input('per_page', 10));
    }

    private function applySearch(BelongsToMany $query, Request $request): void
    {
        $query->when($request->filled('search'), function (Builder $q) use ($request) {
            $q->whereLike('title', "%$request->search%");
        });
    }

    public function toggleWatchListItem(Product $product): void
    {
        $user = Auth::user();

        if ($user?->watchlist()->where('product_id', $product->id)->exists()) {
            $user?->watchlist()->detach($product->id);
            return;
        }

        $user?->watchlist()->attach($product->id);
    }
}
