<?php

namespace App\Filament\Resources\BrandResource\Pages;

use App\Filament\Resources\BrandResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Pages\PageConfiguration;

class CreateBrand extends CreateRecord
{
    protected static string $resource = BrandResource::class;

    protected static ?string $configurationClass = PageConfiguration::class;
}