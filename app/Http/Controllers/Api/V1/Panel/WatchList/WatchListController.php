<?php

namespace App\Http\Controllers\Api\V1\Panel\WatchList;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Products\ProductResource;
use App\Models\Products\Product;
use App\Services\Panel\WatchList\WatchListService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class WatchListController extends Controller
{
    public function __construct(readonly private WatchListService $service)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $watchlist = $this->service->getWatchListedProducts($request);
        return ProductResource::collection($watchlist);
    }

    public function toggle(Product $product): Response
    {
        $this->service->toggleWatchListItem($product);
        return response()->noContent();
    }
}
