<?php

namespace App\Filament\Resources\ReviewResource\Pages;

use App\Filament\Resources\ReviewResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Pages\PageConfiguration;

class CreateReview extends CreateRecord
{
    protected static string $resource = ReviewResource::class;

    protected static ?string $configurationClass = PageConfiguration::class;
}