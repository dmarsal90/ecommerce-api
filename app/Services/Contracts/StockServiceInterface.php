<?php

namespace App\Services\Contracts;

use App\Models\Product;
use App\Models\ProductVariant;

interface StockServiceInterface
{
    public function getAvailableStock(Product|ProductVariant $item): int;

    public function checkStock(Product|ProductVariant $item, int $quantity): bool;

    public function decrementStock(Product|ProductVariant $item, int $quantity): void;

    public function incrementStock(Product|ProductVariant $item, int $quantity): void;

    public function reserveStock(Product|ProductVariant $item, int $quantity): void;

    public function releaseReservedStock(Product|ProductVariant $item, int $quantity): void;
}
