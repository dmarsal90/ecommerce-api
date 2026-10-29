<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductAttributeResource\Pages;
use App\Filament\Resources\ProductAttributeResource\RelationManagers\ValuesRelationManager;
use App\Models\ProductAttribute;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ProductAttributeResource extends Resource
{
    protected static ?string $model = ProductAttribute::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-swatch';

    protected static string|\UnitEnum|null $navigationGroup = 'Catalog';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (string $operation, $state, callable $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),

                TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),

                Select::make('type')
                    ->options([
                        'select' => 'Select',
                        'radio' => 'Radio',
                        'checkbox' => 'Checkbox',
                        'text' => 'Text',
                        'textarea' => 'Textarea',
                    ])
                    ->required()
                    ->default('select'),

                Select::make('display_type')
                    ->options([
                        'dropdown' => 'Dropdown',
                        'swatch' => 'Swatch',
                        'button' => 'Button',
                    ])
                    ->default('dropdown'),

                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0),

                Toggle::make('is_required')
                    ->default(false),

                Toggle::make('is_filterable')
                    ->default(true),

                Toggle::make('is_active')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('slug')->searchable(),
                Tables\Columns\BadgeColumn::make('type')->colors([
                    'primary' => 'select',
                    'success' => 'radio',
                    'warning' => 'checkbox',
                    'gray' => 'text',
                    'info' => 'textarea',
                ]),
                Tables\Columns\IconColumn::make('is_required')->boolean()->label('Required'),
                Tables\Columns\IconColumn::make('is_filterable')->boolean()->label('Filterable'),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('sort_order')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
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
            ValuesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductAttributes::route('/'),
            'create' => Pages\CreateProductAttribute::route('/create'),
            'view' => Pages\ViewProductAttribute::route('/{record}'),
            'edit' => Pages\EditProductAttribute::route('/{record}/edit'),
        ];
    }
}
