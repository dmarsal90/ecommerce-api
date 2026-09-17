<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockMovementResource\Pages;
use App\Models\StockMovement;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StockMovementResource extends Resource
{
    protected static ?string $model = StockMovement::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-path';

    protected static string|\UnitEnum|null $navigationGroup = 'Inventory';
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('inventory_id')
                    ->relationship('inventory', 'sku')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('type')
                    ->options([
                        'in' => 'Stock In',
                        'out' => 'Stock Out',
                        'adjustment' => 'Adjustment',
                        'transfer' => 'Transfer',
                        'reserved' => 'Reserved',
                        'released' => 'Released',
                    ])
                    ->required(),

                TextInput::make('quantity')
                    ->numeric()
                    ->required(),

                TextInput::make('quantity_before')
                    ->numeric()
                    ->required(),

                TextInput::make('quantity_after')
                    ->numeric()
                    ->required(),

                TextInput::make('reference_type')
                    ->maxLength(100),

                TextInput::make('reference_id')
                    ->numeric(),

                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->label('User'),

                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('inventory.sku')->label('SKU')->searchable(),
                Tables\Columns\BadgeColumn::make('type')->colors([
                    'success' => 'in',
                    'danger' => 'out',
                    'warning' => 'adjustment',
                    'info' => 'transfer',
                    'primary' => 'reserved',
                    'gray' => 'released',
                ]),
                Tables\Columns\TextColumn::make('quantity')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('quantity_before')->numeric()->sortable()->label('Before'),
                Tables\Columns\TextColumn::make('quantity_after')->numeric()->sortable()->label('After'),
                Tables\Columns\TextColumn::make('reference_type')->label('Ref Type'),
                Tables\Columns\TextColumn::make('reference_id')->label('Ref ID'),
                Tables\Columns\TextColumn::make('user.name')->label('User'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                DeleteAction::make()
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation(),
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
            'index' => Pages\ListStockMovements::route('/'),
        ];
    }
}