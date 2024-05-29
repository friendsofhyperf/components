<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\OpenAi;

use FriendsOfHyperf\OpenAi\Exception\ApiKeyIsMissing;
use Hyperf\Guzzle\ClientFactory as GuzzleClientFactory;
use OpenAI;
use Psr\Container\ContainerInterface;

use function Hyperf\Config\config;

final class ClientFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $baseUri = config('openai.base_uri', 'api.openai.com/v1');
        $apiKey = config('openai.api_key');
        $organization = config('openai.organization');
        $timeout = config('openai.request_timeout', 30);

        if (! is_string($apiKey) || ($organization !== null && ! is_string($organization))) {
            throw ApiKeyIsMissing::create();
        }

        $httpClient = $container->get(GuzzleClientFactory::class)->create([
            'timeout' => $timeout,
        ]);

        return OpenAI::factory()
            ->withBaseUri($baseUri)
            ->withApiKey($apiKey)
            ->withOrganization($organization)
            ->withHttpHeader('OpenAI-Beta', 'assistants=v2')
            ->withHttpClient($httpClient)
            ->make();
    }
}
