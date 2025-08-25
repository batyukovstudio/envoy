<?php

namespace Batyukovstudio\Envoy\EnvoySection\Envoy\Jobs;

use Batyukovstudio\Envoy\EnvoySection\Envoy\Model\GitLabWebhookCall;
use Spatie\WebhookClient\Jobs\ProcessWebhookJob;
use Spatie\WebhookClient\Models\WebhookCall;
use function collect;
use function dispatch;
use function event;

class ProcessGitLabWebhookJob extends ProcessWebhookJob
{
    /** @var GitLabWebhookCall|WebhookCall */
    public GitLabWebhookCall|WebhookCall $webhookCall;

    public function handle(): void
    {
        // Имена событий:
        // eventName() например: "Merge Request Hook" или "merge_request" (зависит от реализации в вашей модели)
        // eventActionName() например: "Merge Request Hook.merge" или "merge_request.merge"
        event("gitlab-webhooks::{$this->webhookCall->eventName()}", $this->webhookCall);
        event("gitlab-webhooks::{$this->webhookCall->eventActionName()}", $this->webhookCall);

        // Маршрутизация джоб по карте событий
        collect(config('gitlab-webhooks.jobs', []))
            ->filter(function (string $jobClassName, $eventActionName) {
                if ($eventActionName === '*') {
                    return true;
                }
                return in_array($eventActionName, [
                    $this->webhookCall->eventName(),
                    $this->webhookCall->eventActionName(),
                ], true);
            })
            ->each(function (string $jobClassName) {
                if (!class_exists($jobClassName)) {
                    throw new \RuntimeException("Job class not found: {$jobClassName}");
                }
            })
            ->each(fn(string $jobClassName) => dispatch(new $jobClassName($this->webhookCall)));
    }
}