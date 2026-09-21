<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Schema;
use App\Filament\Resources\ProductResource\RelationManagers\VariantsRelationManager;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shopping-bag';

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

                TextInput::make('sku')
                    ->required()
                    ->maxLength(100)
                    ->unique(ignoreRecord: true),

                RichEditor::make('description')
                    ->columnSpanFull(),

                Textarea::make('short_description')
                    ->columnSpanFull(),

                Select::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('brand_id')
                    ->label('Brand')
                    ->relationship('brand', 'name')
                    ->searchable()
                    ->preload(),

                TextInput::make('price')
                    ->numeric()
                    ->step(0.01)
                    ->prefix('$')
                    ->required(),

                TextInput::make('compare_at_price')
                    ->numeric()
                    ->step(0.01)
                    ->prefix('$')
                    ->label('Compare At Price'),

                TextInput::make('cost_price')
                    ->numeric()
                    ->step(0.01)
                    ->prefix('$')
                    ->label('Cost Price'),

                TextInput::make('quantity')
                    ->numeric()
                    ->default(0),

                TextInput::make('low_stock_threshold')
                    ->numeric()
                    ->default(5)
                    ->label('Low Stock Threshold'),

                TextInput::make('material')
                    ->maxLength(255),

                Select::make('gender')
                    ->options([
                        'men' => 'Men',
                        'women' => 'Women',
                        'unisex' => 'Unisex',
                        'kids' => 'Kids',
                    ]),

                Select::make('season')
                    ->options([
                        'spring' => 'Spring',
                        'summer' => 'Summer',
                        'autumn' => 'Autumn',
                        'winter' => 'Winter',
                        'all_season' => 'All Season',
                    ]),

                Toggle::make('is_active')
                    ->default(true),

                Toggle::make('is_featured')
                    ->default(false),

                Toggle::make('track_quantity')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('sku')->searchable(),
                Tables\Columns\TextColumn::make('category.name')->label('Category')->sortable(),
                Tables\Columns\TextColumn::make('brand.name')->label('Brand')->sortable(),
                Tables\Columns\TextColumn::make('price')->money('USD')->sortable(),
                Tables\Columns\TextColumn::make('quantity')->numeric()->sortable()->label('Stock'),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\IconColumn::make('is_featured')->boolean(),
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
            VariantsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view' => Pages\ViewProduct::route('/{record}'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}