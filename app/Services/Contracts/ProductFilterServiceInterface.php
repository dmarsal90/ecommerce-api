<?php

namespace App\Services\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductFilterServiceInterface
{
    public function getFilteredProducts(array $filters): LengthAwarePaginator;
    public function getFeaturedProducts(int $limit = 8): \Illuminate\Database\Eloquent\Collection;
    public function getNewArrivals(int $limit = 8): \Illuminate\Database\Eloquent\Collection;
    public function getOnSaleProducts(int $limit = 8): \Illuminate\Database\Eloquent\Collection;
    public function getProductBySlug(string $slug): ?\App\Models\Product;
    public function getRelatedProducts(\App\Models\Product $product, int $limit = 4): \Illuminate\Database\Eloquent\Collection;
}