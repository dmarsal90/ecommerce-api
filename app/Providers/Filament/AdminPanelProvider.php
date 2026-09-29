<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use App\Filament\Widgets\LowStock;
use App\Filament\Widgets\RecentOrders;
use App\Filament\Widgets\SalesChart;
use App\Filament\Widgets\StatsOverview;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Notifications\Notification;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Schemas\Components\Tabs;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Xplodman\CountUp\CountUpPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->brandName('Sennitwo')
            ->login()
            ->colors([
                'primary' => Color::Blue,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                StatsOverview::class,
                SalesChart::class,
                RecentOrders::class,
                LowStock::class,
                Widgets\AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->userMenuItems([
                'change-avatar' => Action::make('changeAvatar')
                    ->label('Change Avatar')
                    ->icon('heroicon-o-user-circle')
                    ->modalHeading('Choose Your Avatar')
                    ->modalWidth('2xl')
                    ->modalSubmitActionLabel('Apply')
                    ->form([
                        Tabs::make('Tabs')
                            ->tabs([
                                Tabs\Tab::make('Gallery')
                                    ->icon('heroicon-m-photo')
                                    ->schema([
                                        ViewField::make('preset_avatar')
                                            ->hiddenLabel()
                                            ->view('filament-avatar-picker::components.avatar-gallery'),
                                    ]),
                                Tabs\Tab::make('Upload')
                                    ->icon('heroicon-m-arrow-up-tray')
                                    ->schema([
                                        FileUpload::make('custom_upload')
                                            ->hiddenLabel()
                                            ->disk('public')
                                            ->directory('custom-avatars')
                                            ->placeholder('Drag and drop or click to upload')
                                            ->extraAttributes([
                                                'x-on:filepond-processfile' => "setTimeout(() => \$el.closest('.fi-modal').querySelector('button[type=\"submit\"]').click(), 300)",
                                            ]),
                                    ]),
                            ]),
                    ])
                    ->action(function (array $data) {
                        $user = Filament::auth()->user();
                        if (! empty($data['custom_upload'])) {
                            $user->avatar_url = $data['custom_upload'];
                        } elseif (! empty($data['preset_avatar'])) {
                            $user->avatar_url = $data['preset_avatar'];
                        } else {
                            $user->avatar_url = null;
                        }
                        $user->save();

                        Notification::make()
                            ->title('Avatar updated successfully')
                            ->success()
                            ->send();
                    }),
                'change-password' => Action::make('changePassword')
                    ->label('Change Password')
                    ->icon('heroicon-o-lock-closed')
                    ->modalHeading('Change Password')
                    ->modalWidth('lg')
                    ->modalSubmitActionLabel('Update Password')
                    ->form([
                        TextInput::make('current_password')
                            ->label('Current Password')
                            ->password()
                            ->required()
                            ->currentPassword()
                            ->revealable(),
                        TextInput::make('password')
                            ->label('New Password')
                            ->password()
                            ->required()
                            ->minLength(8)
                            ->revealable()
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state)),
                        TextInput::make('password_confirmation')
                            ->label('Confirm New Password')
                            ->password()
                            ->required()
                            ->same('password')
                            ->revealable(),
                    ])
                    ->action(function (array $data) {
                        $user = Filament::auth()->user();
                        $user->password = $data['password'];
                        $user->save();

                        Notification::make()
                            ->title('Password updated successfully')
                            ->success()
                            ->send();
                    }),
            ])
            ->plugins([
                CountUpPlugin::make(),
            ]);
    }
}
