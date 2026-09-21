<?php

namespace App\Filament\Resources\CollectionResource\Pages;

use App\Filament\Resources\CollectionResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Pages\PageConfiguration;

class EditCollection extends EditRecord
{
    protected static string $resource = CollectionResource::class;

    protected static ?string $configurationClass = PageConfiguration::class;
}