<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\JsonResponse;

class BrandController extends Controller
{
    public function index(): JsonResponse
    {
        $brands = Brand::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return response()->json(['data' => $brands]);
    }

    public function featured(): JsonResponse
    {
        $brands = Brand::where('is_active', true)
            ->where('is_featured', true)
            ->orderBy('sort_order')
            ->get();

        return response()->json(['data' => $brands]);
    }

    public function show(string $slug): JsonResponse
    {
        $brand = Brand::with([
            'products' => function ($q) {
                $q->where('is_active', true)->with(['category', 'images'])->latest()->paginate(20);
            }
        ])->where('slug', $slug)->where('is_active', true)->firstOrFail();

        return response()->json(['data' => $brand]);
    }

    public function products(string $slug, \Illuminate\Http\Request $request): JsonResponse
    {
        $brand = Brand::where('slug', $slug)->where('is_active', true)->firstOrFail();
        
        $query = $brand->products()
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
            'data' => $products->items(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
            'brand' => $brand,
        ]);
    }
}