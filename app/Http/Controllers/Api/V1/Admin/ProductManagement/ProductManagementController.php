<?php

namespace App\Http\Controllers\Api\V1\Admin\ProductManagement;

use App\Enums\Products\ProductStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Products\ProductResource;
use App\Models\Products\Product;
use App\Services\Admin\ProductManagement\ProductManagementService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

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

    // View Single Product Details
    public function show($product_id): JsonResponse
    {
        try {
            // Validate that product_id is a positive integer
            validator(['product_id' => $product_id], [
                'product_id' => 'required|integer|min:1',
            ])->validate();

            $response = $this->productService->getProductDetails($product_id);

            return response()->json([
                'success' => true,
                'data' => $response
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid product ID',
                'errors' => $e->errors(),
            ], 400);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }
    }

    // Update Product Status
    public function updateStatus(Request $request, $product_id): JsonResponse
    {
        try {
            // Validate product_id from route
            validator(['product_id' => $product_id], [
                'product_id' => 'required|integer|min:1',
            ])->validate();

            // Validate status from request
            $validated = $request->validate([
                'status' => 'required|integer|in:' . implode(',', array_column(ProductStatus::cases(), 'value'))
            ]);

            $response = $this->productService->updateProductStatus($product_id, $validated['status']);

            return response()->json([
                'success' => true,
                'message' => 'Product status updated successfully!',
                'product' => $response,
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid data provided',
                'errors' => $e->errors(),
            ], 400);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found or already removed!',
            ], 404);
        }
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product status deleted successfully!',
        ]);
    }
}
