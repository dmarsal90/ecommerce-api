<?php

namespace App\Filament\Resources\PermissionResource\Pages;

use App\Filament\Resources\PermissionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Pages\PageConfiguration;

class CreatePermission extends CreateRecord
{
    protected static string $resource = PermissionResource::class;

    protected static ?string $configurationClass = PageConfiguration::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}