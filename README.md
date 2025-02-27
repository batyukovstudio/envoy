# envoy
```bash
composer require batyukovstudio/envoy
```
Для работы требуется прописать и выполнить миграцию:
```bash
php artisan vendor:publish  --tag="bat-envoy"
```
```bash
php artisan migrate
```
необходимые поля в .env:
```php
ROOT_DIRECTORY=
TELEGRAM_BOT_ENVOY_TOKEN=
TELEGRAM_CHAT_ID_FOR_ENVOY=
TELEGRAM_THREAD_ID_FOR_ENVOY
GITHUB_WEBHOOK_SECRET=
DEPLOY_GIT_BRANCH=dev
```
в конфигурационный файл app добавить root_directory со значением ROOT_DIRECTORY
```php
'root_directory' => env('ROOT_DIRECTORY',''),
```
