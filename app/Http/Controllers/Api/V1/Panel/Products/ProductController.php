<?php

namespace App\Http\Controllers\Api\V1\Panel\Products;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Products\ProductResource;
use App\Models\Products\Product;
use App\Services\Panel\Products\ProductService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

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

    public function getDraftProducts(Request $request): AnonymousResourceCollection
    {
        $products = $this->productService->getDraftProducts($request);
        return ProductResource::collection($products);
    }

    public function destroy(Product $product): Response
    {
        $this->productService->deleteProduct($product);
        return response()->noContent();
    }
}
