<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Pages\PageConfiguration;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected static ?string $configurationClass = PageConfiguration::class;
}