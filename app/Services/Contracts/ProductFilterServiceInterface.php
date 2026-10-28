<?php

namespace App\Services\Contracts;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ProductFilterServiceInterface
{
    public function getFilteredProducts(array $filters): LengthAwarePaginator;

    public function getFeaturedProducts(int $limit = 8): Collection;

    public function getNewArrivals(int $limit = 8): Collection;

    public function getOnSaleProducts(int $limit = 8): Collection;

    public function getProductBySlug(string $slug): ?Product;

    public function getRelatedProducts(Product $product, int $limit = 4): Collection;
}
