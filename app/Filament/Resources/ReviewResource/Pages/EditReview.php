<?php

namespace App\Filament\Resources\ReviewResource\Pages;

use App\Filament\Concerns\SavesAndCloses;
use App\Filament\Resources\ReviewResource;
use Filament\Pages\PageConfiguration;
use Filament\Resources\Pages\EditRecord;

class EditReview extends EditRecord
{
    use SavesAndCloses;

    protected static string $resource = ReviewResource::class;

    protected static ?string $configurationClass = PageConfiguration::class;
}
