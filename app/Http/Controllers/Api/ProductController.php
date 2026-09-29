<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ProductFilterRequest;
use App\Http\Resources\Api\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function index(ProductFilterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $query = Product::with(['category', 'brand', 'images', 'variants.attributeValues.attribute'])
            ->where('is_active', true);

        // Filters
        if ($validated['category'] ?? null) {
            $query->whereHas('category', function ($q) use ($validated) {
                $q->where('slug', $validated['category']);
            });
        }

        if ($validated['brand'] ?? null) {
            $query->whereHas('brand', function ($q) use ($validated) {
                $q->where('slug', $validated['brand']);
            });
        }

        if ($validated['gender'] ?? null) {
            $query->where('gender', $validated['gender']);
        }

        if ($validated['season'] ?? null) {
            $query->where('season', $validated['season']);
        }

        if ($validated['featured'] ?? false) {
            $query->where('is_featured', true);
        }

        if ($validated['on_sale'] ?? false) {
            $query->whereNotNull('compare_at_price')
                ->whereColumn('price', '<', 'compare_at_price');
        }

        if ($validated['min_price'] ?? null) {
            $query->where('price', '>=', $validated['min_price']);
        }

        if ($validated['max_price'] ?? null) {
            $query->where('price', '<=', $validated['max_price']);
        }

        if ($validated['search'] ?? null) {
            $query->where(function ($q) use ($validated) {
                $q->where('name', 'like', "%{$validated['search']}%")
                    ->orWhere('description', 'like', "%{$validated['search']}%")
                    ->orWhere('short_description', 'like', "%{$validated['search']}%");
            });
        }

        // Sorting
        $sort = $validated['sort'] ?? 'newest';
        switch ($sort) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        // Pagination
        $perPage = $validated['per_page'] ?? 20;
        $products = $query->paginate($perPage);

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
        $product = Product::with([
            'category',
            'brand',
            'images',
            'variants.attributeValues.attribute',
            'collections',
            'tags',
            'reviews' => function ($q) {
                $q->where('status', 'approved')->with('user')->latest();
            },
        ])->where('slug', $slug)->where('is_active', true)->firstOrFail();

        return (new ProductResource($product))->response();
    }

    public function featured(): JsonResponse
    {
        $products = Product::with(['category', 'brand', 'images'])
            ->where('is_active', true)
            ->where('is_featured', true)
            ->latest()
            ->limit(8)
            ->get();

        return ProductResource::collection($products)->response();
    }

    public function newArrivals(): JsonResponse
    {
        $products = Product::with(['category', 'brand', 'images'])
            ->where('is_active', true)
            ->latest()
            ->limit(8)
            ->get();

        return ProductResource::collection($products)->response();
    }

    public function onSale(): JsonResponse
    {
        $products = Product::with(['category', 'brand', 'images'])
            ->where('is_active', true)
            ->whereNotNull('compare_at_price')
            ->whereColumn('price', '<', 'compare_at_price')
            ->latest()
            ->limit(8)
            ->get();

        return ProductResource::collection($products)->response();
    }

    public function related(string $slug): JsonResponse
    {
        $product = Product::where('slug', $slug)->where('is_active', true)->firstOrFail();

        $products = Product::with(['category', 'brand', 'images'])
            ->where('is_active', true)
            ->where('id', '!=', $product->id)
            ->where(function ($q) use ($product) {
                $q->where('category_id', $product->category_id)
                    ->orWhere('brand_id', $product->brand_id);
            })
            ->inRandomOrder()
            ->limit(4)
            ->get();

        return ProductResource::collection($products)->response();
    }
}
