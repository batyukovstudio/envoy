<?php

namespace Batyukovstudio\Envoy\EnvoySection\Envoy\UI\API\Task;

use Illuminate\Http\Request;
use Spatie\WebhookClient\SignatureValidator\SignatureValidator;
use Spatie\WebhookClient\WebhookConfig;

class GitLabSignatureValidator implements SignatureValidator
{
    public function isValid(Request $request, WebhookConfig $config): bool
    {
        if (!config('gitlab-webhooks.verify_signature')) {
            return true;
        }

        return hash_equals(
            (string)$config->signingSecret,
            (string)$request->header($config->signatureHeaderName)
        );
    }
}