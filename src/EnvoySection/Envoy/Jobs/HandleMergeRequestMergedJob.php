<?php

namespace Batyukovstudio\Envoy\EnvoySection\Envoy\Jobs;

use Batyukovstudio\Envoy\EnvoySection\Envoy\Model\GitLabWebhookCall;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class HandleMergeRequestMergedJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public GitLabWebhookCall $webhookCall;

    public function __construct(GitLabWebhookCall $webhookCall)
    {
        $this->webhookCall = $webhookCall;
    }

    public function handle(): void
    {
        $payload = $this->webhookCall->payload ?? [];

        // GitLab шлёт разные kind'ы; нам нужен merge_request
        if (($payload['object_kind'] ?? null) !== 'merge_request') {
            return;
        }

        $isMerged = ($payload['object_attributes']['state'] ?? null) === 'merged'
            || ($payload['object_attributes']['action'] ?? null) === 'merge';

        // Целевая ветка (аналог base) в GitLab
        $targetBranch = $payload['object_attributes']['target_branch'] ?? null;

        if ($isMerged && $targetBranch === config('gitlab-webhooks.git_branch')) {
            $content = self::getMergeRequestInfo($payload);

            $process = new Process(
                ['vendor/bin/envoy', 'run', 'deploy', "--content={$content}"],
                null,
                ['COMPOSER_HOME' => '/usr/local/bin']
            );

            $process->setWorkingDirectory(config('app.root_directory'));
            $process->run();

            if (!$process->isSuccessful()) {
                Log::error('Envoy ERROR: ' . $process->getOutput());
                throw new ProcessFailedException($process);
            }
        }
    }

    public static function getMergeRequestInfo(array $payload): string
    {
        $content = self::getTitle($payload);

        // Автор MR
        $author = $payload['user']['username']
            ?? $payload['user']['name']
            ?? 'unknown';

        // Кто смёрджил (не всегда приходит)
        $mergedBy = $payload['object_attributes']['merge_user']['username']
            ?? $payload['object_attributes']['merge_user']['name']
            ?? 'unknown';

        $content .= "\n\n<b>Создал MR:</b> {$author}";
        $content .= "\n<b>Смерджил:</b> {$mergedBy}";

        return $content;
    }

    protected static function getTitle(array $payload): string
    {
        $title = $payload['object_attributes']['title'] ?? '';
        $description = $payload['object_attributes']['description'] ?? '';

        // Повтор вашей логики склейки при '…'
        if (method_exists(Str::class, 'position') && Str::position($title, '…') !== false) {
            $firstTitle = Str::substr($title, 0, -1);
            $secondTitle = Str::substr($description, 1);
            return $firstTitle . $secondTitle;
        }

        return $title ?: '(no title)';
    }
}
