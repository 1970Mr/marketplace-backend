<?php

namespace App\Http\Controllers\Api\V1\Products;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Products\ProductResource;
use App\Models\Products\Product;
use App\Services\Products\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    public function __construct(readonly private ProductService $productService)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $products = $this->productService->getFilteredProducts($request);
        return ProductResource::collection($products);
    }

    public function show(Product $product): JsonResponse
    {
        $product->load(['productable', 'user', 'watchers']);
        return ProductResource::make($product)->response();
    }

    public function showProductDetails(Product $product): JsonResponse
    {
        return $this->productService->getProductDetailsResource($product)->response();
    }
}
