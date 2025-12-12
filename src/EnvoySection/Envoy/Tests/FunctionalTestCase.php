<?php

namespace App\Containers\EnvoySection\Envoy\Tests;

use App\Containers\IntegrationSection\Format\Data\Factories\FormatFactory;
use App\Containers\IntegrationSection\SubFormat\Data\Factories\SubFormatFactory;

class FunctionalTestCase extends ContainerTestCase
{
    public function getDeployHookGitlabData(): array
    {
        return [
            'object_kind' => 'merge_request',
            'event_type' => 'merge_request',
            'user' => [
                'username' => 'author'
            ],
            'object_attributes' => [
                'id' => 12345,
                'title' => 'Test MR',
                'description' => 'Body text',
                'state' => 'merged',
                'action' => 'merge',
                'target_branch' => config('gitlab-webhooks.git_branch'),
                'merge_user' => [
                    'username' => 'reviewer'
                ],
            ]
        ];
    }

    public function getDeployHookGitHub(): array
    {
        return [
            'action' => 'closed',
            'pull_request' => [
                'id' => 12345,
                'title' => 'Test PR',
                'merged' => true,
                'base' => [
                    'ref' => config('github-webhooks.git_branch'),
                ],
                'user' => ['login' => 'author'],
                'merged_by' => ['login' => 'reviewer'],
            ],
        ];
    }
}
