<?php

namespace App\Filament\Resources\CollectionResource\Pages;

use App\Filament\Concerns\SavesAndCloses;
use App\Filament\Resources\CollectionResource;
use Filament\Pages\PageConfiguration;
use Filament\Resources\Pages\EditRecord;

class EditCollection extends EditRecord
{
    use SavesAndCloses;

    protected static string $resource = CollectionResource::class;

    protected static ?string $configurationClass = PageConfiguration::class;
}
