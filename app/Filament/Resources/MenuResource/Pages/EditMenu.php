<?php

namespace App\Filament\Resources\MenuResource\Pages;

use App\Filament\Concerns\SavesAndCloses;
use App\Filament\Resources\MenuResource;
use Filament\Pages\PageConfiguration;
use Filament\Resources\Pages\EditRecord;

class EditMenu extends EditRecord
{
    use SavesAndCloses;

    protected static string $resource = MenuResource::class;

    protected static ?string $configurationClass = PageConfiguration::class;
}
