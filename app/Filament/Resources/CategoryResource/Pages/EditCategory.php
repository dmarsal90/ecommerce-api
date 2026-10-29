<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Concerns\SavesAndCloses;
use App\Filament\Resources\CategoryResource;
use Filament\Pages\PageConfiguration;
use Filament\Resources\Pages\EditRecord;

class EditCategory extends EditRecord
{
    use SavesAndCloses;

    protected static string $resource = CategoryResource::class;

    protected static ?string $configurationClass = PageConfiguration::class;
}
