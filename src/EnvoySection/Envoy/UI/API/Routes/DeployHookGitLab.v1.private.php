<?php

use Illuminate\Support\Facades\Route;
use Batyukovstudio\Envoy\EnvoySection\Envoy\UI\API\Controllers\GitLabWebhooksController;

Route::post('deploy-hook-gitlab', [GitLabWebhooksController::class, '__invoke']);
