<?php

namespace App\Filament\Resources\SizeGroupResource\Pages;

use App\Filament\Resources\SizeGroupResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Pages\PageConfiguration;

class CreateSizeGroup extends CreateRecord
{
    protected static string $resource = SizeGroupResource::class;

    protected static ?string $configurationClass = PageConfiguration::class;
}