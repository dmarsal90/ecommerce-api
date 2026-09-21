<?php

namespace App\Filament\Resources\SizeResource\Pages;

use App\Filament\Resources\SizeResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Pages\PageConfiguration;

class ViewSize extends ViewRecord
{
    protected static string $resource = SizeResource::class;

    protected static ?string $configurationClass = PageConfiguration::class;

    protected function getHeaderActions(): array
    {
        return [
            SizeResource\Pages\EditSize::make(),
        ];
    }
}
