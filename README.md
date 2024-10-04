# envoy
```bash
composer require batyukovstudio/envoy
```
Envoy блейд для настройки под проект:
```bash
php artisan vendor:publish  --tag="bat-envoy"
```
Envoy контейнер c route и job для работы с гитом:
```bash
php artisan vendor:publish  --tag="bat-envoy-container"
```
Конфиг гита хука:
```bash
php artisan vendor:publish --provider="Spatie\GitHubWebhooks\GitHubWebhooksServiceProvider" --tag="github-webhooks-config"
```
В конфиге в jobs указать где обрабатываются конкретное действие с гита
```php
'pull_request.closed' => Batyukovstudio\Envoy\EnvoySection\Envoy\Jobs\HandlePullRequestClosedWebhookJob::class,
```
Маграция для гита:
```bash
php artisan vendor:publish --provider="Spatie\GitHubWebhooks\GitHubWebhooksServiceProvider" --tag="github-webhooks-migrations"
```
```bash
php artisan migrate
```
в .env требуется:
```php
ROOT_DIRECTORY=
TELEGRAM_BOT_ENVOY_TOKEN=
TELEGRAM_CHAT_ID_FOR_ENVOY=
GITHUB_WEBHOOK_SECRET=
DEPLOY_GIT_BRANCH=dev
```
