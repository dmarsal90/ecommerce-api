<?php

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Concerns\SavesAndCloses;
use App\Filament\Resources\PageResource;
use Filament\Pages\PageConfiguration;
use Filament\Resources\Pages\EditRecord;

class EditPage extends EditRecord
{
    use SavesAndCloses;

    protected static string $resource = PageResource::class;

    protected static ?string $configurationClass = PageConfiguration::class;
}
