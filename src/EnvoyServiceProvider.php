<?php

namespace Batyukovstudio\Envoy;

use Illuminate\Support\ServiceProvider;


class EnvoyServiceProvider extends ServiceProvider
{
    public array $serviceProviders = [
        // InternalServiceProviderExample::class,
    ];

    public array $aliases = [
        // 'Foo' => Bar::class,
    ];

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/Envoy.blade.php' => './Envoy.blade.php',
            __DIR__ . '/EnvoySection/Envoy/Tests/' => base_path('app/Containers/EnvoySection/Envoy/Tests'),
            __DIR__ . '/EnvoySection/Envoy/UI/API/Routes' => base_path('app/Containers/EnvoySection/Envoy/UI/API/Routes'),
            __DIR__ . '/config/github-webhooks.php' => config_path('github-webhooks.php'),
            __DIR__ . '/config/gitlab-webhooks.php' => config_path('gitlab-webhooks.php'),
        ], 'bat-envoy');

        $this->loadMigrationsFrom(__DIR__ . '/database/migrations/');
    }

    public function register(): void
    {
        parent::register();
    }
}