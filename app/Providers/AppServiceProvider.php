<?php

namespace App\Providers;

use App\Enums\PaymentGatewayType;
use App\Models\User;
use App\Observers\UserObserver;
use App\Services\Payment\FakeGateway;
use App\Services\Payment\PaymentGateway;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PaymentGateway::class, fn (Application $app) => match (
            PaymentGatewayType::from(config('payment.gateway'))
        ) {
            PaymentGatewayType::Fake => $app->make(FakeGateway::class),
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();

        User::observe(UserObserver::class);
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(8)
            : null,
        );
    }
}
