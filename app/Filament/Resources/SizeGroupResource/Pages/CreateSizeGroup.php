<?php

namespace App\Filament\Resources\SizeGroupResource\Pages;

use App\Filament\Resources\SizeGroupResource;
use Filament\Pages\PageConfiguration;
use Filament\Resources\Pages\CreateRecord;

class CreateSizeGroup extends CreateRecord
{
    protected static string $resource = SizeGroupResource::class;

    protected static ?string $configurationClass = PageConfiguration::class;
}
