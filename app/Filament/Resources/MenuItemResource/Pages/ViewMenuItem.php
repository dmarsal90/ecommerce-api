<?php

namespace App\Filament\Resources\MenuItem\Pages;

use App\Filament\Resources\MenuItem\MenuItemResource;
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
