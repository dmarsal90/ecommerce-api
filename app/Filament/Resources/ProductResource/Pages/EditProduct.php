<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Concerns\SavesAndCloses;
use App\Filament\Resources\ProductResource;
use Filament\Pages\PageConfiguration;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    use SavesAndCloses;

    protected static string $resource = ProductResource::class;

    protected static ?string $configurationClass = PageConfiguration::class;
}
