<x-filament-panels::page>
    <x-filament-panels::form
        :form="$this->form"
        :action="fn() => $this->save()"
        :submit="__('filament-panels::resources/pages/edit-record.form.actions.save.label')"
    >
        <x-filament-panels::form.section :columns="2">
            <x-filament-panels::form.grid>
                <x-filament-panels::form.component :component="$this->form->getComponent('name')" />
                <x-filament-panels::form.component :component="$this->form->getComponent('email')" />
            </x-filament-panels::form.grid>
        </x-filament-panels::form.section>

        <x-filament-panels::form.section label="Avatar">
            <x-filament-panels::form.component :component="$this->form->getComponent('avatar_url')" />
        </x-filament-panels::form.section>

        <x-filament-panels::form.actions>
            <x-filament-panels::form.actions.submit />
        </x-filament-panels::form.actions>
    </x-filament-panels::form>
</x-filament-panels::page>
