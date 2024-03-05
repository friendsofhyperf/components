<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Elasticsearch;

use Elastic\Elasticsearch\ClientBuilder;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Guzzle\ClientFactory as GuzzleClientFactory;

class ClientBuilderFactory
{
    public function __construct(protected GuzzleClientFactory $guzzleClientFactory)
    {
    }

    public function create(array $options = [])
    {
        $builder = ClientBuilder::create();

        if (Coroutine::inCoroutine()) {
            $builder->setHttpClient(
                $this->guzzleClientFactory->create($options)
            );
        }

        return $builder;
    }
}