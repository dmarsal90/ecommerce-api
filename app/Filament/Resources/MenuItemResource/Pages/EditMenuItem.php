<?php

namespace App\Filament\Resources\MenuItemResource\Pages;

use App\Filament\Resources\MenuItemResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Pages\PageConfiguration;

class EditMenuItem extends EditRecord
{
    protected static string $resource = MenuItemResource::class;

    protected static ?string $configurationClass = PageConfiguration::class;
}