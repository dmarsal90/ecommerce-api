<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shopping-cart';

    protected static string|\UnitEnum|null $navigationGroup = 'Sales';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('order_number')
                    ->required()
                    ->maxLength(50)
                    ->unique(ignoreRecord: true),

                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Customer'),

                TextInput::make('email')
                    ->email()
                    ->maxLength(255),

                TextInput::make('phone')
                    ->tel()
                    ->maxLength(50),

                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'shipped' => 'Shipped',
                        'delivered' => 'Delivered',
                        'cancelled' => 'Cancelled',
                        'refunded' => 'Refunded',
                    ])
                    ->default('pending')
                    ->required(),

                Select::make('payment_status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'failed' => 'Failed',
                        'refunded' => 'Refunded',
                        'partially_refunded' => 'Partially Refunded',
                    ])
                    ->default('pending')
                    ->required(),

                Select::make('fulfillment_status')
                    ->options([
                        'unfulfilled' => 'Unfulfilled',
                        'partial' => 'Partially Fulfilled',
                        'fulfilled' => 'Fulfilled',
                    ])
                    ->default('unfulfilled'),

                TextInput::make('subtotal')
                    ->numeric()
                    ->step(0.01)
                    ->prefix('$')
                    ->required(),

                TextInput::make('discount')
                    ->numeric()
                    ->step(0.01)
                    ->prefix('$')
                    ->default(0),

                TextInput::make('shipping')
                    ->numeric()
                    ->step(0.01)
                    ->prefix('$')
                    ->default(0),

                TextInput::make('tax')
                    ->numeric()
                    ->step(0.01)
                    ->prefix('$')
                    ->default(0),

                TextInput::make('total')
                    ->numeric()
                    ->step(0.01)
                    ->prefix('$')
                    ->required(),

                TextInput::make('currency')
                    ->maxLength(3)
                    ->default('USD'),

                TextInput::make('payment_method')
                    ->maxLength(100),

                TextInput::make('payment_reference')
                    ->maxLength(255),

                TextInput::make('shipping_method')
                    ->maxLength(100),

                TextInput::make('tracking_number')
                    ->maxLength(255),

                Textarea::make('notes')
                    ->columnSpanFull(),

                DateTimePicker::make('paid_at'),

                DateTimePicker::make('shipped_at'),

                DateTimePicker::make('delivered_at'),

                DateTimePicker::make('cancelled_at'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')->searchable(),
                Tables\Columns\TextColumn::make('user.name')->label('Customer')->searchable(),
                Tables\Columns\BadgeColumn::make('status')->colors([
                    'warning' => 'pending',
                    'info' => 'processing',
                    'primary' => 'shipped',
                    'success' => 'delivered',
                    'danger' => 'cancelled',
                    'gray' => 'refunded',
                ]),
                Tables\Columns\BadgeColumn::make('payment_status')->colors([
                    'warning' => 'pending',
                    'success' => 'paid',
                    'danger' => 'failed',
                    'info' => 'refunded',
                    'primary' => 'partially_refunded',
                ])->label('Payment'),
                Tables\Columns\TextColumn::make('total')->money('USD')->sortable(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
