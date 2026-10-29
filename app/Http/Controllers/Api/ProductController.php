<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ProductFilterRequest;
use App\Http\Resources\Api\ProductResource;
use App\Services\Product\ProductFilterService;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function __construct(
        private ProductFilterService $productFilterService,
    ) {}

    public function index(ProductFilterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $products = $this->productFilterService->getFilteredProducts($validated);

        return response()->json([
            'data' => ProductResource::collection($products)->resolve(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
            'links' => [
                'first' => $products->url(1),
                'last' => $products->url($products->lastPage()),
                'prev' => $products->previousPageUrl(),
                'next' => $products->nextPageUrl(),
            ],
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $product = $this->productFilterService->getProductBySlug($slug);

        if (! $product) {
            abort(404);
        }

        return (new ProductResource($product))->response();
    }

    public function featured(): JsonResponse
    {
        $products = $this->productFilterService->getFeaturedProducts();

        return ProductResource::collection($products)->response();
    }

    public function newArrivals(): JsonResponse
    {
        $products = $this->productFilterService->getNewArrivals();

        return ProductResource::collection($products)->response();
    }

    public function onSale(): JsonResponse
    {
        $products = $this->productFilterService->getOnSaleProducts();

        return ProductResource::collection($products)->response();
    }

    public function related(string $slug): JsonResponse
    {
        $product = $this->productFilterService->getProductBySlug($slug);

        if (! $product) {
            abort(404);
        }

        $products = $this->productFilterService->getRelatedProducts($product);

        return ProductResource::collection($products)->response();
    }
}
