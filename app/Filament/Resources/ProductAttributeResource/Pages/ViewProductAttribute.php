<?php

namespace App\Filament\Resources\ProductAttribute\Pages;

use App\Filament\Resources\ProductAttribute\ProductAttributeResource;
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
