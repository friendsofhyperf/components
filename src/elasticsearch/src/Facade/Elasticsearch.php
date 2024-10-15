<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Elasticsearch\Facade;

use Elastic\Elasticsearch\Client;
use FriendsOfHyperf\Elasticsearch\ClientBuilderFactory;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use InvalidArgumentException;

/**
 * @method static \Elastic\Elasticsearch\Response\Elasticsearch|\Http\Promise\Promise bulk(array $params)
 * @method static \Elastic\Elasticsearch\Response\Elasticsearch|\Http\Promise\Promise count(array $params)
 * @method static \Elastic\Elasticsearch\Response\Elasticsearch|\Http\Promise\Promise create(array $params)
 * @method static \Elastic\Elasticsearch\Response\Elasticsearch|\Http\Promise\Promise delete(array $params)
 * @method static \Elastic\Elasticsearch\Response\Elasticsearch|\Http\Promise\Promise deleteByQuery(array $params)
 * @method static \Elastic\Elasticsearch\Response\Elasticsearch|\Http\Promise\Promise exists(array $params)
 * @method static \Elastic\Elasticsearch\Response\Elasticsearch|\Http\Promise\Promise get(array $params)
 * @method static \Elastic\Elasticsearch\Response\Elasticsearch|\Http\Promise\Promise getSource(array $params)
 * @method static \Elastic\Elasticsearch\Response\Elasticsearch|\Http\Promise\Promise index(array $params)
 * @method static \Elastic\Elasticsearch\Response\Elasticsearch|\Http\Promise\Promise info(array $params)
 * @method static \Elastic\Elasticsearch\Response\Elasticsearch|\Http\Promise\Promise mget(array $params)
 * @method static \Elastic\Elasticsearch\Response\Elasticsearch|\Http\Promise\Promise msearch(array $params)
 * @method static \Elastic\Elasticsearch\Response\Elasticsearch|\Http\Promise\Promise search(array $params)
 * @method static \Elastic\Elasticsearch\Endpoints\Indices indices()
 *
 * @see \Elastic\Elasticsearch\Client
 */
class Elasticsearch
{
    public static function __callStatic(mixed $method, mixed $params): mixed
    {
        return self::connection('default')->{$method}(...$params);
    }

    public static function connection(string $connection = 'default'): Client
    {
        /** @var \Hyperf\Di\Container $container */
        $container = ApplicationContext::getContainer();
        /** @var array{hosts?:string|array} $config */
        $config = $container->get(ConfigInterface::class)->get('elasticsearch.' . $connection, null);

        if ($config == null) {
            throw new InvalidArgumentException(sprintf('Elasticsearch connection [%s] not configured.', $connection));
        }

        /** @var \Elastic\Elasticsearch\ClientBuilder $builder */
        $builder = $container->get(ClientBuilderFactory::class)->create();

        // Set the hosts
        $hosts = $config['hosts'] ?? '';
        $hosts = is_array($hosts) ? $hosts : explode(',', (string) $hosts);
        $builder->setHosts($hosts);

        // Build the client
        return $builder->build();
    }
}
