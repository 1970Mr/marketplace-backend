<?php

namespace App\Http\Controllers\Api\V1\Products;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Products\ProductResource;
use App\Models\Products\Product;
use App\Services\Products\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(readonly private ProductService $productService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $products = $this->productService->getFilteredProducts($request);

        return response()->json([
            'data' => ProductResource::collection($products),
            'meta' => $this->productService->getMetaData($products),
        ]);
    }

    public function show(Product $product): JsonResponse
    {
        $product->load('productable');
        return ProductResource::make($product)->response();
    }
}
