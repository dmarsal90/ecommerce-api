<?php

namespace App\Filament\Resources\TagResource\Pages;

use App\Filament\Resources\TagResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Pages\PageConfiguration;

class CreateTag extends CreateRecord
{
    protected static string $resource = TagResource::class;

    protected static ?string $configurationClass = PageConfiguration::class;
}