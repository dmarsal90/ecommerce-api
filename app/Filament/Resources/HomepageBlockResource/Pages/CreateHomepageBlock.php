<?php

namespace App\Filament\Resources\HomepageBlockResource\Pages;

use App\Filament\Resources\HomepageBlockResource;
use Filament\Pages\PageConfiguration;
use Filament\Resources\Pages\CreateRecord;

class CreateHomepageBlock extends CreateRecord
{
    protected static string $resource = HomepageBlockResource::class;

    protected static ?string $configurationClass = PageConfiguration::class;
}
