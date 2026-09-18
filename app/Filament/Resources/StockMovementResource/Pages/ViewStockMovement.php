<?php

namespace App\Filament\Resources\StockMovement\Pages;

use App\Filament\Resources\StockMovement\StockMovementResource;
use Filament\Resources\Pages\ViewRecord;

class ViewStockMovement extends ViewRecord
{
    protected static string $resource = StockMovementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            StockMovementResource\Pages\EditStockMovement::make(),
        ];
    }
}
