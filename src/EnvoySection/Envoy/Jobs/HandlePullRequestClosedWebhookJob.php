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
            ($branchName === 'main' || $branchName === 'develop')
        ) {
            if (Str::position($this->webhookCall->payload['pull_request']['title'], "…") !== false) {
                $firstTitle = Str::substr($this->webhookCall->payload['pull_request']['title'], 0, -1);
                $secondTitle = Str::substr($this->webhookCall->payload['pull_request']['body'], 1);
                $content = $firstTitle . $secondTitle;
            } else {
                $content = $this->webhookCall->payload['pull_request']['title'];
            }
            $content = $content . "\n\n" . "Создал пул: " . $this->webhookCall->payload['pull_request']['user']['login'];
            $content = $content . "\n" . "Проверил пул: " . $this->webhookCall->payload['pull_request']['merged_by']['login'];
            $content=$content."\n\n"."*bold \*text* _italic \*text_ __underline__ ~strikethrough~ ||spoiler|| <b>bold</b>, <strong>bold</strong> <i>italic</i>, <em>italic</em> <u>underline</u>, <ins>underline</ins>
<s>strikethrough</s>,  <del>strikethrough</del>";
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
}
