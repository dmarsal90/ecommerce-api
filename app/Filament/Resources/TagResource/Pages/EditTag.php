<?php

namespace App\Filament\Resources\TagResource\Pages;

use App\Filament\Resources\TagResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Pages\PageConfiguration;

class EditTag extends EditRecord
{
    protected static string $resource = TagResource::class;

    protected static ?string $configurationClass = PageConfiguration::class;
}