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

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\MissingParameterException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Elastic\Elasticsearch\Response\Elasticsearch;
use FriendsOfHyperf\Elasticsearch\ClientBuilderFactory;
use Http\Promise\Promise;
use Hyperf\Contract\StdoutLoggerInterface;
use Psr\Container\ContainerInterface;

class EntriesIndex
{
    private ClientBuilderFactory $clientBuilderFactory;

    private ?StdoutLoggerInterface $logger = null;

    public function __construct(
        private ContainerInterface $container,
        public string $index = 'telescope_entries',
        private array $options = [],
    ) {
        $this->clientBuilderFactory = $this->container->get(ClientBuilderFactory::class);

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
        } catch (ClientResponseException $e) {
            $this->logger?->error('the 4xx error', ['message' => $e->getMessage()]);
        } catch (MissingParameterException $e) {
            $this->logger?->error('the 5xx error', ['message' => $e->getMessage()]);
        } catch (ServerResponseException $e) {
            $this->logger?->error('network error like NoNodeAvailableException', ['message' => $e->getMessage()]);
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
        } catch (ClientResponseException $e) {
            $this->logger?->error('the 4xx error', ['message' => $e->getMessage()]);
        } catch (MissingParameterException $e) {
            $this->logger?->error('the 5xx error', ['message' => $e->getMessage()]);
        } catch (ServerResponseException $e) {
            $this->logger?->error('network error like NoNodeAvailableException', ['message' => $e->getMessage()]);
        }
    }

    public function exists(): bool
    {
        try {
            return $this->client()
                ->indices()
                ->exists([
                    'index' => $this->index,
                ])
                ->getStatusCode() !== 404;
        } catch (ClientResponseException $e) {
            $this->logger?->error('the 4xx error', ['message' => $e->getMessage()]);
        } catch (MissingParameterException $e) {
            $this->logger?->error('the 5xx error', ['message' => $e->getMessage()]);
        } catch (ServerResponseException $e) {
            $this->logger?->error('network error like NoNodeAvailableException', ['message' => $e->getMessage()]);
        }

        return false;
    }

    public function client(): Client
    {
        $options = $this->options;
        $clientBuilderFactory = $this->clientBuilderFactory->create([]);
        if (isset($options['hosts'])) {
            $clientBuilderFactory->setHosts((array) $options['hosts']);
        }
        if (isset($options['username'], $options['password'])) {
            $clientBuilderFactory->setBasicAuthentication($options['username'], $options['password']);
        }
        return $clientBuilderFactory->create()->build();
    }
}
