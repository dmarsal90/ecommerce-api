<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\Product;
use App\Policies\OrderPolicy;
use App\Policies\ProductPolicy;
use App\Services\Cart\CartService;
use App\Services\Contracts\PaymentGatewayInterface;
use App\Services\Contracts\PricingCalculatorInterface;
use App\Services\Contracts\ProductFilterServiceInterface;
use App\Services\Contracts\StockServiceInterface;
use App\Services\Inventory\StockLockService;
use App\Services\Order\CheckoutService;
use App\Services\Order\OrderService;
use App\Services\Order\PricingCalculator;
use App\Services\Payment\StripePaymentGateway;
use App\Services\Product\ProductFilterService;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\HtmlString;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->loadTranslationsFrom(
            __DIR__.'/../../vendor/filament/forms/resources/lang',
            'filament-forms'
        );

        $this->app->bind(StockServiceInterface::class, StockLockService::class);
        $this->app->bind(PricingCalculatorInterface::class, PricingCalculator::class);
        $this->app->bind(ProductFilterServiceInterface::class, ProductFilterService::class);
        $this->app->bind(PaymentGatewayInterface::class, StripePaymentGateway::class);
        $this->app->singleton(CartService::class);
        $this->app->singleton(OrderService::class);
        $this->app->singleton(CheckoutService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Order::class, OrderPolicy::class);
        Gate::policy(Product::class, ProductPolicy::class);

        // Card-testing and enumeration guard for every payment endpoint.
        RateLimiter::for('payments', fn (Request $request) => Limit::perMinute(10)->by($request->user()?->id ?: $request->ip()));

        // Branded, translated emails; the language comes from User::preferredLocale().
        VerifyEmail::toMailUsing(fn ($user, string $url) => (new MailMessage)
            ->subject(__('mail.verify_subject'))
            ->greeting(__('mail.hello', ['name' => $user->name]))
            ->line(__('mail.verify_intro', ['app' => config('app.name')]))
            ->action(__('mail.verify_action'), $url)
            ->line(__('mail.expire', ['count' => config('auth.verification.expire', 60)]))
            ->salutation(new HtmlString(__('mail.bye').'<br>'.e(config('app.name')))));

        ResetPassword::toMailUsing(fn ($user, string $token) => (new MailMessage)
            ->subject(__('mail.reset_subject'))
            ->greeting(__('mail.hello', ['name' => $user->name]))
            ->line(__('mail.reset_intro', ['app' => config('app.name')]))
            ->action(__('mail.reset_action'), config('app.frontend_url').'/reset-password?token='.$token.'&email='.urlencode($user->getEmailForPasswordReset()))
            ->line(__('mail.reset_expire', ['count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire')]))
            ->salutation(new HtmlString(__('mail.bye').'<br>'.e(config('app.name')))));

    }
}
