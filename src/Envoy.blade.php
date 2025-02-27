@servers(['localhost' => '127.0.0.1'])

@include('vendor/autoload.php')

@setup
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
    $gitBranch=$_SERVER['DEPLOY_GIT_BRANCH'];
    if(!($rootPath = $_SERVER['ROOT_DIRECTORY'] ?? false)) { throw new Exception('--ROOT_DIRECTORY must be specified'); }
    if(!($messageThreadId = $_SERVER['TELEGRAM_THREAD_ID_FOR_ENVOY'] ?? false)) { throw new Exception('--TELEGRAM_THREAD_ID_FOR_ENVOY must be specified'); }
    if(!($telegramBotToken = $_SERVER['TELEGRAM_BOT_ENVOY_TOKEN'] ?? false)) { throw new Exception('--TELEGRAM_BOT_ENVOY_TOKEN must be specified'); }
    if(!($telegramChatId = $_SERVER['TELEGRAM_CHAT_ID_FOR_ENVOY'] ?? false)) { throw new Exception('--TELEGRAM_CHAT_ID_FOR_ENVOY must be specified'); }
    if(!($nodePacakageManager = $_SERVER['NODE_PACKAGE_MANAGER'] ?? 'npm')) { throw new Exception('--NODE_PACKAGE_MANAGER must be specified'); }
    if(!($nodeVersion = $_SERVER['NODE_VERSION'] ?? '20.18.2')) { throw new Exception('--NODE_VERSION must be specified'); }
@endsetup

@error
    @telegram($telegramBotToken, $telegramChatId, "🔥<b>Ошибка обновления</b>🔥 \n ". $task, ["parse_mode"=>"HTML", "message_thread_id"=>"$messageThreadId"])
@enderror

@success
    @telegram($telegramBotToken, $telegramChatId, "<b>Сервер обновлён</b>   👉👈 \n\n {$content}", ["parse_mode"=>"HTML", "message_thread_id"=>"$messageThreadId"])
@endsuccess

{{-- Main Task --}}

@story('deploy')
    update-code
    install-dependencies
    run-migrates
    restart-queues
{{--    generate-docs--}}
    clear-cache
    update-cache
    build-front  {{--По хорошему добавить парметр в .evv --}}
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
    git pull origin {{$gitBranch}}
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

@task('build-front')
    cd {{ $rootPath }}
    export NVM_DIR="$HOME/.nvm"
                      [ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"
                      [ -s "$NVM_DIR/bash_completion" ] && \. "$NVM_DIR/bash_completion"
                      export PATH=$PATH:{{ $nodeVersion }}
    {{$nodePacakageManager}} install
    {{$nodePacakageManager}} run build
@endtask
