<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MenuItemResource\Pages;
use App\Models\MenuItem;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MenuItemResource extends Resource
{
    protected static ?string $model = MenuItem::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-link';

    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Grid::make(2)->schema([
                    Select::make('menu_id')
                        ->label('Menu')
                        ->relationship('menu', 'name')
                        ->required()
                        ->live()
                        ->preload(),

                    Select::make('parent_id')
                        ->label('Parent item')
                        ->relationship('parent', 'label', fn ($query, Get $get) => $query->where('menu_id', $get('menu_id')))
                        ->placeholder('None (top level)'),

                    TextInput::make('label')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('url')
                        ->maxLength(255)
                        ->placeholder('/collections/spring'),

                    Select::make('target')
                        ->options(['_self' => 'Same tab', '_blank' => 'New tab'])
                        ->default('_self')
                        ->required(),

                    TextInput::make('sort_order')
                        ->numeric()
                        ->default(0)
                        ->minValue(0),

                    Toggle::make('open_in_new_tab')
                        ->label('Open in new tab'),

                    Toggle::make('is_active')
                        ->label('Active')
                        ->default(true),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('label')->searchable()->sortable(),
                TextColumn::make('menu.name')->label('Menu')->badge()->sortable(),
                TextColumn::make('parent.label')->label('Parent')->placeholder('Top level'),
                TextColumn::make('url')->placeholder('-')->limit(40),
                TextColumn::make('sort_order')->sortable(),
                IconColumn::make('is_active')->label('Active')->boolean(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                SelectFilter::make('menu_id')->label('Menu')->relationship('menu', 'name'),
            ])
            ->actions([
                ViewAction::make()->iconButton()->color('info'),
                EditAction::make()->iconButton()->color('warning'),
                DeleteAction::make()
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->iconButton(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMenuItems::route('/'),
            'create' => Pages\CreateMenuItem::route('/create'),
            'view' => Pages\ViewMenuItem::route('/{record}'),
            'edit' => Pages\EditMenuItem::route('/{record}/edit'),
        ];
    }
}
