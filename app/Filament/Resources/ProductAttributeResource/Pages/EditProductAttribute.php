<?php

namespace App\Filament\Resources\ProductAttributeResource\Pages;

use App\Filament\Resources\ProductAttributeResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Pages\PageConfiguration;

class EditProductAttribute extends EditRecord
{
    protected static string $resource = ProductAttributeResource::class;

    protected static ?string $configurationClass = PageConfiguration::class;
}