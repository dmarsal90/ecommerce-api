<?php

namespace App\Filament\Resources\BannerResource\Pages;

use App\Filament\Resources\BannerResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Pages\PageConfiguration;

class EditBanner extends EditRecord
{
    protected static string $resource = BannerResource::class;

    protected static ?string $configurationClass = PageConfiguration::class;
}