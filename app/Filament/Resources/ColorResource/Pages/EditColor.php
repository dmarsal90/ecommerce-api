<?php

namespace App\Filament\Resources\ColorResource\Pages;

use App\Filament\Concerns\SavesAndCloses;
use App\Filament\Resources\ColorResource;
use Filament\Pages\PageConfiguration;
use Filament\Resources\Pages\EditRecord;

class EditColor extends EditRecord
{
    use SavesAndCloses;

    protected static string $resource = ColorResource::class;

    protected static ?string $configurationClass = PageConfiguration::class;
}
