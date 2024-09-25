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
        $this->registerPublishing();
        $process = new Process(['vendor:publish', "--tag=bat-envoy"]);
        $process->run();
    }

    public function register(): void
    {
        parent::register();
    }

    protected function registerPublishing()
    {
        $this->publishes([
            __DIR__ . '/Envoy.blade.php' => base_path(),
        ], 'bat-envoy');
    }
}