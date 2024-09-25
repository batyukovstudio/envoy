@servers(['localhost' => '127.0.0.1'])

@include('vendor/autoload.php')

@setup
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
    if(!($rootPath = $_SERVER['ROOT_DIRECTORY'] ?? false)) { throw new Exception('--ROOT_DIRECTORY must be specified'); }
    if(!($telegramBotToken = $_SERVER['TELEGRAM_BOT_ENVOY_TOKEN'] ?? false)) { throw new Exception('--TELEGRAM_BOT_ENVOY_TOKEN must be specified'); }
    if(!($telegramChatId = $_SERVER['TELEGRAM_CHAT_ID_FOR_ENVOY'] ?? false)) { throw new Exception('--TELEGRAM_CHAT_ID_FOR_ENVOY must be specified'); }
@endsetup

@error
    @telegram($telegramBotToken, $telegramChatId, "!!!Ошибка обновления!!! \n ". $task)
@enderror

@success
    @telegram($telegramBotToken, $telegramChatId, "Сервер обновлен {$content}")
@endsuccess

{{-- Main Task --}}

@story('deploy')
    update-code
    install-dependencies
    run-migrates
    restart-queues
    generate-docs
    clear-cache
    update-cache
@endstory


{{-- Helper Tasks --}}

@task('run-migrates')
    cd {{ $rootPath }};
    php artisan migrate --force
@endtask

@task('restart-queues')
    cd {{ $rootPath }};
    php artisan queue:restart
@endtask

@task('update-code')
    cd {{ $rootPath }};
    git pull
@endtask

@task('install-dependencies')
    cd {{ $rootPath }};
    composer install
@endtask

@task('generate-docs')
    cd {{ $rootPath }};
    php artisan swagger:generate
@endtask

@task('clear-cache')
    cd {{ $rootPath }};
    php artisan optimize:clear
@endtask

@task('update-cache')
    cd {{ $rootPath }};
    php artisan optimize
@endtask
