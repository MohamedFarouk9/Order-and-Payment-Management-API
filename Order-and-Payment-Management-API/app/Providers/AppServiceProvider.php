<?php

namespace App\Providers;

use App\Actions\ConfirmOrderAction;
use App\Actions\CreateOrderAction;
use App\Actions\DeleteOrderAction;
use App\Actions\ProcessPaymentAction;
use App\Actions\RegisterUserAction;
use App\Actions\UpdateOrderAction;
use App\Services\AuthService;
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Services\PaymentGateway\CreditCardGateway;
use App\Services\PaymentGateway\PayPalGateway;
use App\Services\PaymentGateway\PaymentGatewayManager;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * Service Container Configuration:
     * - PaymentGatewayManager: Singleton (only one instance)
     * - Action classes: Bind for automatic dependency injection
     * - Service classes: Factory methods for complex dependencies
     */
    public function register(): void
    {
        // Register Payment Gateway Manager as a singleton
        $this->app->singleton(PaymentGatewayManager::class, function ($app) {
            $manager = new PaymentGatewayManager();

            // Register available payment gateways
            // To add new payment method: create class, register here
            $manager->register('credit_card', new CreditCardGateway());
            $manager->register('paypal', new PayPalGateway());

            return $manager;
        });

        // Register Action classes for automatic injection
        $this->app->bind(CreateOrderAction::class, CreateOrderAction::class);
        $this->app->bind(UpdateOrderAction::class, UpdateOrderAction::class);
        $this->app->bind(ConfirmOrderAction::class, ConfirmOrderAction::class);
        $this->app->bind(DeleteOrderAction::class, DeleteOrderAction::class);
        $this->app->bind(RegisterUserAction::class, RegisterUserAction::class);

        // ProcessPaymentAction needs PaymentGatewayManager dependency
        $this->app->bind(ProcessPaymentAction::class, function ($app) {
            return new ProcessPaymentAction(
                $app->make(PaymentGatewayManager::class)
            );
        });

        // Register Service classes with their action dependencies
        $this->app->bind(OrderService::class, function ($app) {
            return new OrderService(
                $app->make(CreateOrderAction::class),
                $app->make(UpdateOrderAction::class),
                $app->make(ConfirmOrderAction::class),
                $app->make(DeleteOrderAction::class)
            );
        });

        $this->app->bind(PaymentService::class, function ($app) {
            return new PaymentService(
                $app->make(ProcessPaymentAction::class)
            );
        });

        $this->app->bind(AuthService::class, function ($app) {
            return new AuthService(
                $app->make(RegisterUserAction::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
