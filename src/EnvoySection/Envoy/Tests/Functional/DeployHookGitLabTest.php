<?php

namespace App\Containers\EnvoySection\Envoy\Tests\Functional\API;

use Apiato\Core\Exceptions\MissingTestEndpointException;
use Apiato\Core\Exceptions\UndefinedMethodException;
use Apiato\Core\Exceptions\WrongEndpointFormatException;
use App\Containers\EnvoySection\Envoy\Tests\FunctionalTestCase;
use Batyukovstudio\Envoy\EnvoySection\Envoy\Jobs\HandlePullRequestClosedWebhookJob;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
class DeployHookGitLabTest extends FunctionalTestCase
{
    protected string $endpoint = 'post@v1/deploy-hook-gitlab';

    protected array $access = [
        'permissions' => null,
        'roles' => null,
    ];

    /**
     * @throws MissingTestEndpointException
     * @throws WrongEndpointFormatException
     * @throws UndefinedMethodException
     */
    public function test_deploy_hook_gitlab_returns_ok(): void
    {
        $response = $this->makeCall(
            data: $this->getDeployHookGitlabData(),
            headers: [
                'X-Gitlab-Token' => config('gitlab-webhooks.signing_secret'),
                'X-Gitlab-Event' => 'Merge Request Hook',
            ]
        );

        $response->assertOk();
    }

}
