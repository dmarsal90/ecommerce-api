<?php

namespace App\Filament\Resources\Menu\Pages;

use App\Filament\Resources\Menu\MenuResource;
use Filament\Resources\Pages\ViewRecord;

class ViewMenu extends ViewRecord
{
    protected static string $resource = MenuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            MenuResource\Pages\EditMenu::make(),
        ];
    }
}
