<?php

namespace App\Filament\Resources\TagResource\Pages;

use App\Filament\Concerns\SavesAndCloses;
use App\Filament\Resources\TagResource;
use Filament\Pages\PageConfiguration;
use Filament\Resources\Pages\EditRecord;

class EditTag extends EditRecord
{
    use SavesAndCloses;

    protected static string $resource = TagResource::class;

    protected static ?string $configurationClass = PageConfiguration::class;
}
