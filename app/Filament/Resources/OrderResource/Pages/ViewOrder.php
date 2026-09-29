<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Pages\PageConfiguration;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected static ?string $configurationClass = PageConfiguration::class;

    protected function getHeaderActions(): array
    {
        return [
            EditOrder::make(),
        ];
    }
}
