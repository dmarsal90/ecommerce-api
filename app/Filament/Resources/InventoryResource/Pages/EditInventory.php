<?php

namespace App\Filament\Resources\InventoryResource\Pages;

use App\Filament\Concerns\SavesAndCloses;
use App\Filament\Resources\InventoryResource;
use Filament\Pages\PageConfiguration;
use Filament\Resources\Pages\EditRecord;

class EditInventory extends EditRecord
{
    use SavesAndCloses;

    protected static string $resource = InventoryResource::class;

    protected static ?string $configurationClass = PageConfiguration::class;
}
