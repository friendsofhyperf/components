<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\TelescopeElasticsearch\Storage;

use Elastic\Elasticsearch\Response\Elasticsearch;
use Exception;
use Http\Promise\Promise;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Coroutine\Coroutine;
use Psr\Container\ContainerInterface;

class EntriesIndex
{
    private ?StdoutLoggerInterface $logger = null;

    public function __construct(
        private ContainerInterface $container,
        public string $index = 'telescope_entries',
        private array $options = [],
    ) {
        if ($this->container->has(StdoutLoggerInterface::class)) {
            $this->logger = $this->container->get(StdoutLoggerInterface::class);
        }
    }

    /**
     * @return Elasticsearch|Promise|void
     */
    public function create()
    {
        try {
            return $this->client()->indices()->create([
                'index' => $this->index,
                'body' => [
                    'settings' => [
                        'index' => [
                            'number_of_shards' => 1,
                            'number_of_replicas' => 0,
                        ],
                    ],
                    'mappings' => [
                        '_source' => [
                            'enabled' => true,
                        ],
                        'properties' => $this->properties(),
                    ],
                ],
            ]);
        } catch (Exception $e) {
            $this->logger?->error((string) $e);
        }
    }

    public function properties(): array
    {
        return [
            'uuid' => [
                'type' => 'keyword',
            ],
            'batch_id' => [
                'type' => 'keyword',
            ],
            'family_hash' => [
                'type' => 'keyword',
            ],
            'should_display_on_index' => [
                'type' => 'boolean',
                'null_value' => true,
            ],
            'type' => [
                'type' => 'keyword',
            ],
            'content' => [
                'type' => 'object',
                'dynamic' => false,
            ],
            'tags' => [
                'type' => 'nested',
                'dynamic' => false,
                'properties' => [
                    'raw' => [
                        'type' => 'keyword',
                    ],
                    'name' => [
                        'type' => 'keyword',
                    ],
                    'value' => [
                        'type' => 'keyword',
                    ],
                ],
            ],
            'created_at' => [
                'type' => 'date',
                'format' => 'yyyy-MM-dd HH:mm:ss',
            ],
            '@timestamp' => [
                'type' => 'date',
            ],
        ];
    }

    public function delete(): void
    {
        try {
            $this->client()->indices()->delete([
                'index' => $this->index,
            ]);
        } catch (Exception $e) {
            $this->logger?->error((string) $e);
        }
    }

    public function exists(): bool
    {
        try {
            /** @var bool|Elasticsearch $exists */
            $exists = $this->client()->indices()->exists([
                'index' => $this->index,
            ]);
            return is_bool($exists) ? $exists : $exists->getStatusCode() !== 404;
        } catch (Exception $e) {
            $this->logger?->error((string) $e);
        }

        return false;
    }

    /**
     * @return \Elastic\Elasticsearch\Client|\Elasticsearch\Client
     */
    public function client()
    {
        $options = $this->options;
        $clientBuilder = $this->getClientBuilderFactory()->create([]);
        if (isset($options['hosts'])) {
            $clientBuilder->setHosts((array) $options['hosts']);
        }
        if (isset($options['username'], $options['password'])) {
            $clientBuilder->setBasicAuthentication($options['username'], $options['password']);
        }
        return $clientBuilder->create()->build();
    }

    /**
     * @return \Elasticsearch\ClientBuilder|\Elastic\Elasticsearch\ClientBuilder
     */
    private function getClientBuilderFactory()
    {
        $guzzleClientFactory = $this->container->get(\Hyperf\Guzzle\ClientFactory::class);
        if (class_exists('Elastic\Elasticsearch\ClientBuilder')) {
            $builder = \Elastic\Elasticsearch\ClientBuilder::create();
            $builder->setHttpClient(
                $guzzleClientFactory->create()
            );

            return $builder;
        }

        if (class_exists('Elasticsearch\ClientBuilder')) {
            $builder = \Elasticsearch\ClientBuilder::create();
            if (Coroutine::inCoroutine()) {
                $builder->setHandler(new \Hyperf\Guzzle\RingPHP\CoroutineHandler());
            }

            return $builder;
        }

        throw new Exception('Please install elasticsearch/elasticsearch');
    }
}
