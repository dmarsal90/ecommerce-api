<?php

namespace App\Filament\Resources\StockMovementResource\Pages;

use App\Filament\Resources\StockMovementResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;

class ListStockMovements extends ListRecords
{
    protected static string $resource = StockMovementResource::class;
}