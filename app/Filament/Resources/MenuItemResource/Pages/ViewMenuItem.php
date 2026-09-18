<?php

namespace App\Filament\Resources\MenuItemResource\Pages;

use App\Filament\Resources\MenuItemResource;
use Filament\Resources\Pages\ViewRecord;

class ViewMenuItem extends ViewRecord
{
    protected static string $resource = MenuItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            MenuItemResource\Pages\EditMenuItem::make(),
        ];
    }
}
