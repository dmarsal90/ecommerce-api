<?php

namespace App\Filament\Resources\HomepageBlockResource\Pages;

use App\Filament\Resources\HomepageBlockResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Pages\PageConfiguration;

class CreateHomepageBlock extends CreateRecord
{
    protected static string $resource = HomepageBlockResource::class;

    protected static ?string $configurationClass = PageConfiguration::class;
}