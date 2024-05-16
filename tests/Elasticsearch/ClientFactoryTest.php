<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use Elastic\Elasticsearch\ClientBuilder;
use Elastic\Transport\Exception\NoNodeAvailableException;
use FriendsOfHyperf\Elasticsearch\ClientBuilderFactory;
use GuzzleHttp\Client;
use Hyperf\Guzzle\ClientFactory as GuzzleClientFactory;

test('test ClientBuilderFactoryCreate', function () {
    /** @var GuzzleClientFactory $clientFactory */
    $clientFactory = $this->mock(GuzzleClientFactory::class, function ($mock) {
        $mock->shouldReceive('create')->once()->with([])->andReturn(new Client());
    });
    $clientFactory = new ClientBuilderFactory($clientFactory);

    $clientBuilder = $clientFactory->create();

    $this->assertInstanceOf(ClientBuilder::class, $clientBuilder);
});

test('test HostNotReached', function () {
    $this->markTestSkipped('Skip testHostNotReached');

    $this->expectException(NoNodeAvailableException::class);

    /** @var GuzzleClientFactory $clientFactory */
    $clientFactory = $this->mock(GuzzleClientFactory::class, function ($mock) {
        $mock->shouldReceive('create')->once()->with([])->andReturn(new Client());
    });
    $clientFactory = new ClientBuilderFactory($clientFactory);

    $client = $clientFactory->create()->setHosts(['http://127.0.0.1:9201'])->build();

    $client->info();
});
