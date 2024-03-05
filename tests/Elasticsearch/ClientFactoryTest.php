<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Tests\Elasticsearch;

use Elastic\Elasticsearch\ClientBuilder;
use FriendsOfHyperf\Elasticsearch\ClientBuilderFactory;
use FriendsOfHyperf\Tests\TestCase;
use GuzzleHttp\Client;
use Hyperf\Guzzle\ClientFactory as GuzzleClientFactory;
use PHPUnit\Framework\Attributes\CoversNothing;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class ClientFactoryTest extends TestCase
{
    public function testClientBuilderFactoryCreate()
    {
        /** @var GuzzleClientFactory $clientFactory */
        $clientFactory = $this->mock(GuzzleClientFactory::class, function ($mock) {
            $mock->shouldReceive('create')->once()->with([])->andReturn(new Client());
        });
        $clientFactory = new ClientBuilderFactory($clientFactory);

        $clientBuilder = $clientFactory->create();

        $this->assertInstanceOf(ClientBuilder::class, $clientBuilder);
    }
}
