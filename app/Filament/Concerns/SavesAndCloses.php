<?php

namespace App\Filament\Concerns;

use Filament\Actions\Action;

/** Adds a "Save & close" button to an EditRecord page: saves, then goes to the resource list. */
trait SavesAndCloses
{
    protected bool $closeAfterSave = false;

    public function saveAndClose(): void
    {
        $this->closeAfterSave = true;
        $this->save();
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction(),
            Action::make('saveAndClose')->label('Save & close')->color('gray')->action('saveAndClose'),
            $this->getCancelFormAction(),
        ];
    }

    protected function getRedirectUrl(): ?string
    {
        return $this->closeAfterSave ? static::getResource()::getUrl('index') : parent::getRedirectUrl();
    }
}
