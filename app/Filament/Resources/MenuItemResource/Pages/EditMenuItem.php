<?php

namespace App\Filament\Resources\MenuItemResource\Pages;

use App\Filament\Concerns\SavesAndCloses;
use App\Filament\Resources\MenuItemResource;
use Filament\Pages\PageConfiguration;
use Filament\Resources\Pages\EditRecord;

class EditMenuItem extends EditRecord
{
    use SavesAndCloses;

    protected static string $resource = MenuItemResource::class;

    protected static ?string $configurationClass = PageConfiguration::class;
}
