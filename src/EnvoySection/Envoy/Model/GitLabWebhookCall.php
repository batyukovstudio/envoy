<?php

namespace Batyukovstudio\Envoy\EnvoySection\Envoy\Model;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Support\Arr;
use Spatie\WebhookClient\Models\WebhookCall;

class GitLabWebhookCall extends WebhookCall
{
    use MassPrunable;

    protected $table = 'gitlab_webhook_calls';

    // имя события из заголовка GitLab
    public function eventName(): string
    {
        return (string)$this->headerBag()->get('X-Gitlab-Event', $this->payload('object_kind') ?? '');
    }

    // “действие” для удобной маршрутизации
    public function eventActionName(): string
    {
        // для MR GitLab кладёт action в object_attributes.action (open, merge, close и т.п.)
        $action = $this->payload('object_attributes.action') ?? null;

        if ($action) {
            return "{$this->eventName()}.$action";
        }

        // fallback: используем object_kind (merge_request, push, pipeline, …)
        return $this->payload('object_kind') ?? $this->eventName();
    }

    public function payload(string $key = null): mixed
    {
        return is_null($key) ? $this->payload : Arr::get($this->payload, $key);
    }

    public function prunable(): Builder
    {
        $days = (int)config('gitlab-webhooks.prune_webhook_calls_after_days', 30);
        return static::query()->where('created_at', '<=', now()->subDays($days));
    }
}