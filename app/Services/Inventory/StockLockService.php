<?php

namespace App\Services\Inventory;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\Contracts\StockServiceInterface;
use Illuminate\Support\Facades\DB;

class StockLockService implements StockServiceInterface
{
    public function getAvailableStock(Product|ProductVariant $item): int
    {
        return DB::transaction(function () use ($item) {
            $lockedItem = $this->lockForUpdate($item);

            return $lockedItem->inventory?->available ?? $lockedItem->quantity;
        });
    }

    public function checkStock(Product|ProductVariant $item, int $quantity): bool
    {
        if (! $item->track_quantity) {
            return true;
        }

        $available = $this->getAvailableStock($item);

        return $available >= $quantity;
    }

    public function decrementStock(Product|ProductVariant $item, int $quantity): void
    {
        if (! $item->track_quantity) {
            return;
        }

        DB::transaction(function () use ($item, $quantity) {
            $lockedItem = $this->lockForUpdate($item);
            $lockedItem->decrement('quantity', $quantity);
            $lockedItem->inventory?->decrement('available', $quantity);
        });
    }

    public function incrementStock(Product|ProductVariant $item, int $quantity): void
    {
        if (! $item->track_quantity) {
            return;
        }

        DB::transaction(function () use ($item, $quantity) {
            $lockedItem = $this->lockForUpdate($item);
            $lockedItem->increment('quantity', $quantity);
            $lockedItem->inventory?->increment('available', $quantity);
        });
    }

    public function reserveStock(Product|ProductVariant $item, int $quantity): void
    {
        if (! $item->track_quantity) {
            return;
        }

        DB::transaction(function () use ($item, $quantity) {
            $lockedItem = $this->lockForUpdate($item);
            $lockedItem->inventory?->increment('reserved', $quantity);
            $lockedItem->inventory?->decrement('available', $quantity);
        });
    }

    public function releaseReservedStock(Product|ProductVariant $item, int $quantity): void
    {
        if (! $item->track_quantity) {
            return;
        }

        DB::transaction(function () use ($item, $quantity) {
            $lockedItem = $this->lockForUpdate($item);
            $lockedItem->inventory?->decrement('reserved', $quantity);
            $lockedItem->inventory?->increment('available', $quantity);
        });
    }

    private function lockForUpdate(Product|ProductVariant $item): Product|ProductVariant
    {
        $driver = DB::connection()->getDriverName();

        // SQLite doesn't support FOR UPDATE, but uses database-level locking during transactions
        // So we can just fetch the row without lockForUpdate() in SQLite
        if ($driver === 'sqlite') {
            return match (true) {
                $item instanceof ProductVariant => ProductVariant::where('id', $item->id)->firstOrFail(),
                default => Product::where('id', $item->id)->firstOrFail(),
            };
        }

        // For PostgreSQL/MySQL, use row-level locking
        return match (true) {
            $item instanceof ProductVariant => ProductVariant::where('id', $item->id)->lockForUpdate()->firstOrFail(),
            default => Product::where('id', $item->id)->lockForUpdate()->firstOrFail(),
        };
    }
}
