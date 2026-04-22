# batyukovstudio/envoy

Пакет для deploy-процессов через Laravel Envoy с webhook-интеграциями GitHub/GitLab и Telegram-уведомлениями.

## 1. Установка

```bash
composer require batyukovstudio/envoy
```

## 2. Публикация файлов

После установки опубликуйте файлы пакета:

```bash
php artisan vendor:publish --tag="bat-envoy"
```

Публикуются конфиги, маршруты, шаблон `Envoy.blade.php`, тесты контейнера и helper для Telegram (`deploy/telegram.php`).

## 3. Миграции

После публикации выполните миграции:

```bash
php artisan migrate
```

## 4. Настройка `.env`

Добавьте и заполните переменные:

```env
ROOT_DIRECTORY=
PHP_COMMAND=php
BUILD_FRONT=true
TELEGRAM_BOT_ENVOY_TOKEN=
TELEGRAM_CHAT_ID_FOR_ENVOY=
TELEGRAM_THREAD_ID_FOR_ENVOY=
TELEGRAM_PROXY=
GITHUB_WEBHOOK_SECRET=
DEPLOY_GIT_BRANCH=dev

GITLAB_TARGET_BRANCH=
GITLAB_WEBHOOK_SECRET=

NODE_PACKAGE_MANAGER=
NODE_VERSION=
```

Обязательные:
- `ROOT_DIRECTORY`
- `PHP_COMMAND` (если не указано, используется `php`)
- `TELEGRAM_BOT_ENVOY_TOKEN`
- `TELEGRAM_CHAT_ID_FOR_ENVOY`
- `GITHUB_WEBHOOK_SECRET`
- `DEPLOY_GIT_BRANCH`

Опциональные:
- `BUILD_FRONT` (по умолчанию `true`)
- `TELEGRAM_THREAD_ID_FOR_ENVOY` (для отправки в конкретный thread/topic)
- `TELEGRAM_PROXY` (если нужен прокси для Telegram Bot API)
- `NODE_PACKAGE_MANAGER` и `NODE_VERSION` (обязательны только при `BUILD_FRONT=true`)

Поддерживаемые форматы `TELEGRAM_PROXY`:
- `http://user:pass@host:port`
- `https://user:pass@host:port`
- `socks5://user:pass@host:port`
- `socks5h://user:pass@host:port`

Поведение deploy:
- `BUILD_FRONT=true` — в `deploy` story выполняется задача `build-front`.
- `BUILD_FRONT=false` — задача `build-front` пропускается.
- `PHP_COMMAND=php8.3` (пример) — все команды `artisan` выполняются через указанную команду PHP.

Примечание: для `.env.testing` переменная `ROOT_DIRECTORY` должна указывать путь до тестируемого проекта.

## 5. Настройка `config/app.php`

Добавьте в массив `return [...]` файла `config/app.php`:

```php
'root_directory' => env('ROOT_DIRECTORY', ''),
```

Это значение используется Envoy-задачами как рабочая директория проекта при выполнении deploy-команд.

## 6. Обновление пакета

Если пакет уже установлен и вышла новая версия:

```bash
composer update batyukovstudio/envoy
php artisan vendor:publish --tag="bat-envoy" --force
php artisan migrate
```

Важно про `--force`:
- `--force` перезаписывает уже опубликованные файлы.
- Если вы вносили локальные правки в `Envoy.blade.php`, `deploy/telegram.php`, конфиги или маршруты, сначала сохраните diff и проверьте изменения перед перезаписью.
- Перепубликация обычно нужна, когда в новой версии изменились publishable-файлы (README/changelog релиза это обычно отражает).

После обновления проверьте:
- появились ли новые env-переменные;
- нужны ли дополнительные миграции;
- не конфликтуют ли ваши локальные правки с новой версией опубликованных файлов.

## 7. Сценарий: установка с нуля

1. Установить пакет (`composer require ...`).
2. Опубликовать файлы (`vendor:publish --tag="bat-envoy"`).
3. Выполнить миграции (`php artisan migrate`).
4. Заполнить `.env`.
5. Проверить `config/app.php` и наличие `root_directory`.

## 8. Сценарий: обновление пакета

1. Обновить пакет (`composer update batyukovstudio/envoy`).
2. Сверить changelog/README новой версии.
3. При необходимости перепубликовать файлы (`vendor:publish --tag="bat-envoy" --force`).
4. Проверить новые env-переменные.
5. Выполнить миграции (`php artisan migrate`).
