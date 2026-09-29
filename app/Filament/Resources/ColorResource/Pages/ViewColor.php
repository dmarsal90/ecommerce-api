<?php

namespace App\Filament\Resources\ColorResource\Pages;

use App\Filament\Resources\ColorResource;
use Filament\Pages\PageConfiguration;
use Filament\Resources\Pages\ViewRecord;

class ViewColor extends ViewRecord
{
    protected static string $resource = ColorResource::class;

    protected static ?string $configurationClass = PageConfiguration::class;

    protected function getHeaderActions(): array
    {
        return [
            EditColor::make(),
        ];
    }
}
