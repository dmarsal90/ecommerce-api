<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Concerns\SavesAndCloses;
use App\Filament\Resources\RoleResource;
use Filament\Actions;
use Filament\Pages\PageConfiguration;
use Filament\Resources\Pages\EditRecord;

class EditRole extends EditRecord
{
    use SavesAndCloses;

    protected static string $resource = RoleResource::class;

    protected static ?string $configurationClass = PageConfiguration::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
