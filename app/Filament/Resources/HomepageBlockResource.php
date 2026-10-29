<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HomepageBlockResource\Pages;
use App\Models\HomepageBlock;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class HomepageBlockResource extends Resource
{
    protected static ?string $model = HomepageBlock::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-squares-2x2';

    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Grid::make(2)->schema([
                    Select::make('type')
                        ->options(fn () => HomepageBlock::getTypes() + HomepageBlock::query()->distinct()->pluck('type', 'type')->map(fn ($type) => str($type)->headline()->toString())->all())
                        ->required(),

                    TextInput::make('title')
                        ->maxLength(255),

                    DatePicker::make('start_date'),

                    DatePicker::make('end_date')
                        ->afterOrEqual('start_date'),

                    TextInput::make('sort_order')
                        ->numeric()
                        ->default(0)
                        ->minValue(0),

                    Toggle::make('is_active')
                        ->label('Active')
                        ->default(true),
                ]),

                Textarea::make('settings')
                    ->label('Settings (JSON)')
                    ->helperText('Block-specific configuration, as read by the storefront.')
                    ->rows(14)
                    ->rules(['nullable', 'json'])
                    ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : $state)
                    ->dehydrateStateUsing(fn ($state) => filled($state) ? json_decode($state, true) : null)
                    ->extraInputAttributes(['class' => 'font-mono text-xs'])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable()->sortable()->placeholder('-'),
                TextColumn::make('type')->badge()->formatStateUsing(fn ($state) => HomepageBlock::getTypes()[$state] ?? str($state)->headline()->toString()),
                TextColumn::make('sort_order')->sortable(),
                TextColumn::make('start_date')->date()->placeholder('-'),
                TextColumn::make('end_date')->date()->placeholder('-'),
                IconColumn::make('is_active')->label('Active')->boolean(),
            ])
            ->defaultSort('sort_order')
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
            'index' => Pages\ListHomepageBlocks::route('/'),
            'create' => Pages\CreateHomepageBlock::route('/create'),
            'view' => Pages\ViewHomepageBlock::route('/{record}'),
            'edit' => Pages\EditHomepageBlock::route('/{record}/edit'),
        ];
    }
}
