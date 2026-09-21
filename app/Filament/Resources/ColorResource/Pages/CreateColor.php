<?php

namespace App\Filament\Resources\ColorResource\Pages;

use App\Filament\Resources\ColorResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Pages\PageConfiguration;

class CreateColor extends CreateRecord
{
    protected static string $resource = ColorResource::class;

    protected static ?string $configurationClass = PageConfiguration::class;
}