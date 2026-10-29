<?php

namespace App\Filament\Resources\BannerResource\Pages;

use App\Filament\Concerns\SavesAndCloses;
use App\Filament\Resources\BannerResource;
use Filament\Pages\PageConfiguration;
use Filament\Resources\Pages\EditRecord;

class EditBanner extends EditRecord
{
    use SavesAndCloses;

    protected static string $resource = BannerResource::class;

    protected static ?string $configurationClass = PageConfiguration::class;
}
