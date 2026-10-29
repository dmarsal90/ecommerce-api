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
use Illuminate\Support\Facades\Gate;
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

        ResetPassword::createUrlUsing(fn ($user, string $token) => config('app.frontend_url').'/reset-password?token='.$token.'&email='.urlencode($user->getEmailForPasswordReset()));
    }
}
