<?php

namespace Batyukovstudio\Envoy;

use Illuminate\Support\ServiceProvider;
use Symfony\Component\Process\Process;



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
    }

    public function register(): void
    {
        parent::register();
    }
}