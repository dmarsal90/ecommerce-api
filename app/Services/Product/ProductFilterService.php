<?php

namespace App\Services\Product;

use App\Models\Product;
use App\Services\Contracts\ProductFilterServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ProductFilterService implements ProductFilterServiceInterface
{
    public function getFilteredProducts(array $filters): LengthAwarePaginator
    {
        $query = Product::with(['category', 'brand', 'images', 'variants.attributeValues.attribute'])
            ->where('is_active', true);

        $this->applyFilters($query, $filters);
        $this->applySorting($query, $filters['sort'] ?? 'newest');

        $perPage = $filters['per_page'] ?? 20;
        return $query->paginate($perPage);
    }

    public function getFeaturedProducts(int $limit = 8): Collection
    {
        return Product::with(['category', 'brand', 'images'])
            ->where('is_active', true)
            ->where('is_featured', true)
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function getNewArrivals(int $limit = 8): Collection
    {
        return Product::with(['category', 'brand', 'images'])
            ->where('is_active', true)
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function getOnSaleProducts(int $limit = 8): Collection
    {
        return Product::with(['category', 'brand', 'images'])
            ->where('is_active', true)
            ->whereNotNull('compare_at_price')
            ->whereColumn('price', '<', 'compare_at_price')
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function getProductBySlug(string $slug): ?Product
    {
        return Product::with([
            'category',
            'brand',
            'images',
            'variants.attributeValues.attribute',
            'collections',
            'tags',
            'reviews' => function ($q) {
                $q->where('status', 'approved')->with('user')->latest();
            },
        ])->where('slug', $slug)->where('is_active', true)->first();
    }

    public function getRelatedProducts(Product $product, int $limit = 4): Collection
    {
        return Product::with(['category', 'brand', 'images'])
            ->where('is_active', true)
            ->where('id', '!=', $product->id)
            ->where(function ($q) use ($product) {
                $q->where('category_id', $product->category_id)
                    ->orWhere('brand_id', $product->brand_id);
            })
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }

    private function applyFilters($query, array $filters): void
    {
        if (! empty($filters['category'])) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $filters['category']));
        }

        if (! empty($filters['brand'])) {
            $query->whereHas('brand', fn ($q) => $q->where('slug', $filters['brand']));
        }

        if (! empty($filters['gender'])) {
            $query->where('gender', $filters['gender']);
        }

        if (! empty($filters['season'])) {
            $query->where('season', $filters['season']);
        }

        if (! empty($filters['featured'])) {
            $query->where('is_featured', true);
        }

        if (! empty($filters['on_sale'])) {
            $query->whereNotNull('compare_at_price')
                ->whereColumn('price', '<', 'compare_at_price');
        }

        if (! empty($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (! empty($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%");
            });
        }
    }

    private function applySorting($query, string $sort): void
    {
        $sortMap = [
            'price_asc' => ['price', 'asc'],
            'price_desc' => ['price', 'desc'],
            'name_asc' => ['name', 'asc'],
            'name_desc' => ['name', 'desc'],
            'oldest' => ['created_at', 'asc'],
            'newest' => ['created_at', 'desc'],
        ];

        [$column, $direction] = $sortMap[$sort] ?? ['created_at', 'desc'];
        $query->orderBy($column, $direction);
    }
}