<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Concerns\SavesAndCloses;
use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Pages\PageConfiguration;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    use SavesAndCloses;

    protected static string $resource = UserResource::class;

    protected static ?string $configurationClass = PageConfiguration::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
