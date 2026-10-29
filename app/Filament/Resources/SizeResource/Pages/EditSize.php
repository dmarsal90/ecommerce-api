<?php

namespace App\Filament\Resources\SizeResource\Pages;

use App\Filament\Concerns\SavesAndCloses;
use App\Filament\Resources\SizeResource;
use Filament\Pages\PageConfiguration;
use Filament\Resources\Pages\EditRecord;

class EditSize extends EditRecord
{
    use SavesAndCloses;

    protected static string $resource = SizeResource::class;

    protected static ?string $configurationClass = PageConfiguration::class;
}
