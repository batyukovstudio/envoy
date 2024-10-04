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
        ], 'bat-envoy');

        $this->publishes([
            __DIR__ . '/EnvoySection' => './app/Containers/EnvoySection',
        ], 'bat-envoy-container');
    }

    public function register(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/EnvoySection/UI/API/Routes/DeployHook.v1.php');
        parent::register();
    }
}