<?php

namespace App\Filament\Resources\ProductAttributeResource\Pages;

use App\Filament\Resources\ProductAttributeResource;
use Filament\Resources\Pages\ViewRecord;

class ViewProductAttribute extends ViewRecord
{
    protected static string $resource = ProductAttributeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ProductAttributeResource\Pages\EditProductAttribute::make(),
        ];
    }
}
