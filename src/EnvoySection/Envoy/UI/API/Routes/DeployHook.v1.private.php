<?php

use Illuminate\Support\Facades\Route;
use Spatie\GitHubWebhooks\Http\Controllers\GitHubWebhooksController;

Route::post('deploy-hook', [GitHubWebhooksController::class, '__invoke']);
