<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\CollectionResource;
use App\Http\Resources\Api\ProductResource;
use App\Models\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CollectionController extends Controller
{
    public function index(): JsonResponse
    {
        $collections = Collection::where('is_active', true)
            ->orderBy('sort_order')
            ->withCount('products')
            ->get();

        return CollectionResource::collection($collections)->response();
    }

    public function show(string $slug): JsonResponse
    {
        $collection = Collection::with([
            'products' => function ($q) {
                $q->where('is_active', true)->with(['category', 'brand', 'images'])->latest()->paginate(20);
            },
        ])->where('slug', $slug)->where('is_active', true)->firstOrFail();

        return (new CollectionResource($collection))->response();
    }

    public function products(string $slug, Request $request): JsonResponse
    {
        $collection = Collection::where('slug', $slug)->where('is_active', true)->firstOrFail();

        $query = $collection->products()
            ->with(['category', 'brand', 'images'])
            ->where('is_active', true);

        if ($request->has('sort')) {
            switch ($request->sort) {
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
                case 'newest':
                default:
                    $query->orderBy('created_at', 'desc');
                    break;
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $perPage = min($request->get('per_page', 20), 100);
        $products = $query->paginate($perPage);

        return response()->json([
            'data' => ProductResource::collection($products)->resolve(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
            'collection' => new CollectionResource($collection)->resolve(),
        ]);
    }
}
