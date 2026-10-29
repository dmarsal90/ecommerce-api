<?php

namespace App\Filament\Resources\ProductAttributeResource\Pages;

use App\Filament\Concerns\SavesAndCloses;
use App\Filament\Resources\ProductAttributeResource;
use Filament\Pages\PageConfiguration;
use Filament\Resources\Pages\EditRecord;

class EditProductAttribute extends EditRecord
{
    use SavesAndCloses;

    protected static string $resource = ProductAttributeResource::class;

    protected static ?string $configurationClass = PageConfiguration::class;
}
