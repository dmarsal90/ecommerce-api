<?php

namespace App\Filament\Resources\MenuResource\Pages;

use App\Filament\Resources\MenuResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Pages\PageConfiguration;

class CreateMenu extends CreateRecord
{
    protected static string $resource = MenuResource::class;

    protected static ?string $configurationClass = PageConfiguration::class;
}