<?php

namespace App\Filament\Resources\Collection\Pages;

use App\Filament\Resources\Collection\CollectionResource;
use Filament\Resources\Pages\ViewRecord;

class ViewCollection extends ViewRecord
{
    protected static string $resource = CollectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CollectionResource\Pages\EditCollection::make(),
        ];
    }
}
