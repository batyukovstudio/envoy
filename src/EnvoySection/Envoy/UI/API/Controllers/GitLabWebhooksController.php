<?php

namespace Batyukovstudio\Envoy\EnvoySection\Envoy\UI\API\Controllers;

use Batyukovstudio\Envoy\EnvoySection\Envoy\UI\API\Task\GitLabSignatureValidator;
use Illuminate\Http\Request;
use Spatie\WebhookClient\Exceptions\InvalidConfig;
use Spatie\WebhookClient\Exceptions\InvalidWebhookSignature;
use Spatie\WebhookClient\WebhookConfig;
use Spatie\WebhookClient\WebhookProcessor;
use Symfony\Component\HttpFoundation\Response;

class GitLabWebhooksController
{
    /**
     * @throws InvalidConfig
     */
    public function __invoke(Request $request): \Illuminate\Http\JsonResponse
    {
        $webhookConfig = new WebhookConfig([
            'name' => 'GitLab',
            'signing_secret' => config('gitlab-webhooks.signing_secret'), // norm
            'signature_header_name' => 'X-Gitlab-Token', // norm
            'signature_validator' => GitLabSignatureValidator::class, // norm
            'webhook_profile' => config('gitlab-webhooks.profile'), // norm
            'webhook_model' => config('gitlab-webhooks.model'),
            'process_webhook_job' => config('gitlab-webhooks.job'),
            'store_headers' => [
                'X-Gitlab-Event',
                'X-Gitlab-Token',
            ],
        ]);

        try {
            (new WebhookProcessor($request, $webhookConfig))->process();
        } catch (InvalidWebhookSignature) {
            return response()->json(['message' => 'invalid signature'], Response::HTTP_FORBIDDEN);
        }

        return response()->json(['message' => 'ok']);
    }
}
