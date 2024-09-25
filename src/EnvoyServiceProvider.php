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
        $this->registerPublishing();
    }

    public function register(): void
    {
        parent::register();
    }

    protected function registerPublishing()
    {
        $this->publishes([
            __DIR__ . '/Envoy.blade.php' => '/',
        ], 'bat-envoy');
    }
}