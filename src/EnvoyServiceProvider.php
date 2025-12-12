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
            __DIR__ . '/EnvoySection/Envoy/UI/API/Routes/DeployHook.v1.private.php',
            __DIR__ . '/EnvoySection/Envoy/Tests/' => base_path('app/Containers/EnvoySection/Envoy/Tests'),
//            __DIR__ . '/EnvoySection/Envoy/UI/API/Routes/DeployHook.v1.private.php' => './app/Containers/EnvoySection/Envoy/UI/API/Routes/DeployHook.v1.private.php',
            __DIR__ . '/EnvoySection/Envoy/UI/API/Routes/DeployHookGitLab.v1.private.php',
//            __DIR__ . '/EnvoySection/Envoy/UI/API/Routes/DeployHookGitLab.v1.private.php' => './app/Containers/EnvoySection/Envoy/UI/API/Routes/DeployHookGitLab.v1.private.php',
            __DIR__ . '/config/github-webhooks.php' => config_path('github-webhooks.php'),
            __DIR__ . '/config/gitlab-webhooks.php' => config_path('gitlab-webhooks.php'),
        ], 'bat-envoy');

        $this->loadMigrationsFrom(__DIR__ . '/database/migrations/');

        $this->publishes([
            __DIR__ . '/EnvoySection/Envoy/Jobs/',
        ], 'bat-envoy-job');
//        $this->publishes([
//            __DIR__ . '/EnvoySection/Envoy/Jobs/' => './app/Containers/EnvoySection/Envoy/Jobs/',
//        ], 'bat-envoy-job');

        $this->publishes([
            __DIR__ . '/EnvoySection/Envoy/UI/API/Routes',
        ], 'bat-githook_route');

//        $this->publishes([
//            __DIR__ . '/EnvoySection/Envoy/UI/API/Routes' => './app/Containers/EnvoySection/Envoy/UI/API/Routes',
//        ], 'bat-githook_route');
    }

    public function register(): void
    {
        parent::register();
    }
}