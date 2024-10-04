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
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/Envoy.blade.php' => './Envoy.blade.php',
            ], 'bat-envoy');
        }
        $this->publishes([
            __DIR__ . '/EnvoySection/Envoy/Jobs/' => './app/Containers/EnvoySection/Envoy/Jobs/',
        ], 'bat-envoy-job');

        $this->publishes([
            __DIR__ . '/EnvoySection/Envoy/UI/API/Routes' => './app/Containers/EnvoySection/Envoy/UI/API/Routes',
        ], 'bat-git-hook');
        $this->loadRoutesFrom(__DIR__ . '/EnvoySection/Envoy/UI/API/Routes/DeployHook.v1.private.php');

    }

    public function register(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/EnvoySection/Envoy/UI/API/Routes/DeployHook.v1.private.php');
        parent::register();
    }
}