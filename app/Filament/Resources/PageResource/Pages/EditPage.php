<?php

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Resources\PageResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Pages\PageConfiguration;

class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;

    protected static ?string $configurationClass = PageConfiguration::class;
}