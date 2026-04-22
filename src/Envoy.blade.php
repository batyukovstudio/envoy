@servers(['localhost' => '127.0.0.1'])

@setup
    require_once __DIR__ . '/vendor/autoload.php';
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
    require_once __DIR__ . '/deploy/telegram.php';

    $gitBranch=$_SERVER['DEPLOY_GIT_BRANCH'];
    $phpCommand = $_SERVER['PHP_COMMAND'] ?? 'php';
    $buildFront = filter_var($_SERVER['BUILD_FRONT'] ?? 'true', FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    if ($buildFront === null) { $buildFront = true; }

    if(!($rootPath = $_SERVER['ROOT_DIRECTORY'] ?? false)) { throw new Exception('--ROOT_DIRECTORY must be specified'); }
    if ($buildFront) {
        if(!($nodePackageManager = $_SERVER['NODE_PACKAGE_MANAGER'] ?? 'npm')) { throw new Exception('--NODE_PACKAGE_MANAGER must be specified when BUILD_FRONT=true'); }
        if(!($nodeVersion = $_SERVER['NODE_VERSION'] ?? '20.18.2')) { throw new Exception('--NODE_VERSION must be specified when BUILD_FRONT=true'); }
    }
@endsetup

@error
    try {
        notifyDeployError((string) $task);
    } catch (\Throwable $exception) {
        fwrite(STDERR, '[telegram] Failed to send error notification: ' . $exception->getMessage() . PHP_EOL);
    }
@enderror

@success
    try {
        notifyDeploySuccess((string) $content);
    } catch (\Throwable $exception) {
        fwrite(STDERR, '[telegram] Failed to send success notification: ' . $exception->getMessage() . PHP_EOL);
    }
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
    @if($buildFront)
    build-front
    @endif
@endstory


{{-- Helper Tasks --}}

@task('run-migrates')
    cd {{ $rootPath }};
    {{ $phpCommand }} artisan migrate --force
@endtask

@task('restart-queues')
    cd {{ $rootPath }};
    {{ $phpCommand }} artisan queue:restart
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
    {{ $phpCommand }} artisan swagger:generate
@endtask

@task('clear-cache')
    cd {{ $rootPath }};
    {{ $phpCommand }} artisan optimize:clear
@endtask

@task('update-cache')
    cd {{ $rootPath }};
    {{ $phpCommand }} artisan optimize
@endtask

@task('build-front')
    cd {{ $rootPath }}
    export NVM_DIR="$HOME/.nvm"
                      [ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"
                      [ -s "$NVM_DIR/bash_completion" ] && \. "$NVM_DIR/bash_completion"
                      export PATH=$PATH:{{ $nodeVersion }}
    {{$nodePackageManager}} install
    {{$nodePackageManager}} run build
@endtask
