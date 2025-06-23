<?php

namespace App\Http\Controllers\Api\V1\Admin\ProductManagement;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Admin\ProductManagement\ChangeProductStatusRequest;
use App\Http\Resources\V1\Products\ProductResource;
use App\Models\Products\Product;
use App\Services\Admin\ProductManagement\ProductManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ProductManagementController extends Controller
{
    public function __construct(readonly private ProductManagementService $productService)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $products = $this->productService->getFilteredProducts($request->all());
        return ProductResource::collection($products);
    }

    public function show(Product $product): JsonResponse
    {
        return response()->json(
            $this->productService->getProductDetails($product)
        );
    }

    public function changeStatus(ChangeProductStatusRequest $request, Product $product): JsonResponse
    {
        $product->update(['status' => $request->get('status')]);
        return ProductResource::make($product->load(['user', 'productable']))->response();
    }

    public function destroy(Product $product): Response
    {
        $product->delete();
        return response()->noContent();
    }
}
