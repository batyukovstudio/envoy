<?php

namespace App\Containers\EnvoySection\Envoy\Tests\Functional\API;

use Apiato\Core\Exceptions\MissingTestEndpointException;
use Apiato\Core\Exceptions\UndefinedMethodException;
use Apiato\Core\Exceptions\WrongEndpointFormatException;
use App\Containers\EnvoySection\Envoy\Tests\FunctionalTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
class DeployHookTest extends FunctionalTestCase
{
    protected string $endpoint = 'post@v1/deploy-hook';

    protected array $access = [
        'permissions' => null,
        'roles' => null,
    ];

    /**
     * @throws MissingTestEndpointException
     * @throws WrongEndpointFormatException
     * @throws UndefinedMethodException
     */
    public function test_deploy_hook_returns_ok(): void
    {
        $jsonPayload = json_encode($this->getDeployHookGitHub());
        $secret = config('github-webhooks.signing_secret');
        $signature = 'sha256=' . hash_hmac('sha256', $jsonPayload, $secret);

        $response = $this->makeCall(
            data: $this->getDeployHookGitHub(),
            headers: [
                'X-GitHub-Event' => 'pull_request',
                'X-Hub-Signature-256' => $signature,
            ]
        );
        $response->assertOk();
    }
}
