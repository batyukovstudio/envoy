<?php

namespace Batyukovstudio\Envoy\EnvoySection\Envoy\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Spatie\GitHubWebhooks\Models\GitHubWebhookCall;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class HandlePullRequestClosedWebhookJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public GitHubWebhookCall $gitHubWebhookCall;

    public function __construct(
        public GitHubWebhookCall $webhookCall
    )
    {
    }

    public function handle()
    {
        $branchName = $this->webhookCall->payload['pull_request']['base']['ref'] ?? null;
        if ($this->webhookCall->payload['pull_request']['merged'] &&
            ($branchName === config('github-webhooks.git_branch'))
        ) {
            $content = self::getPullRequestInfo($this->webhookCall->payload);

            $process = new Process(['vendor/bin/envoy', 'run', 'deploy', "--content=" . $content], null, [
                'COMPOSER_HOME' => '/usr/local/bin',
            ]);
            $process->setWorkingDirectory(config('app.root_directory'));
            $process->run();

            if (!$process->isSuccessful()) {
                Log::error('Envoy ERROR: ' . $process->getOutput());
                throw new ProcessFailedException($process);
            }
        }
    }

    public static function getPullRequestInfo(array $payload): string
    {
        $content = self::getTitle($payload);
        $content = $content . "\n\n" . "<b>Создал пул:</b> " . $payload['pull_request']['user']['login'];
        $content = $content . "\n" . "<b>Проверил пул:</b> " . $payload['pull_request']['merged_by']['login'];
        return $content;
    }

    protected static function getTitle(array $payload): string
    {
        if (Str::position($payload['pull_request']['title'], "…") !== false) {
            $firstTitle = Str::substr($payload['pull_request']['title'], 0, -1);
            $secondTitle = Str::substr($payload['pull_request']['body'], 1);
            $content = $firstTitle . $secondTitle;
        } else {
            $content = $payload['pull_request']['title'];
        }
        return $content;
    }
}
