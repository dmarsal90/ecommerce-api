<?php

namespace App\Filament\Resources\HomepageBlockResource\Pages;

use App\Filament\Concerns\SavesAndCloses;
use App\Filament\Resources\HomepageBlockResource;
use Filament\Pages\PageConfiguration;
use Filament\Resources\Pages\EditRecord;

class EditHomepageBlock extends EditRecord
{
    use SavesAndCloses;

    protected static string $resource = HomepageBlockResource::class;

    protected static ?string $configurationClass = PageConfiguration::class;
}
