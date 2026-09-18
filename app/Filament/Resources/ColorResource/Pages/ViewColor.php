<?php

namespace App\Filament\Resources\Color\Pages;

use App\Filament\Resources\Color\ColorResource;
use Filament\Resources\Pages\ViewRecord;

class ViewColor extends ViewRecord
{
    protected static string $resource = ColorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ColorResource\Pages\EditColor::make(),
        ];
    }
}
