<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Sentry\Tracing\Aspect;

use FriendsOfHyperf\Sentry\Feature;
use FriendsOfHyperf\Sentry\SentryContext;
use Hyperf\Context\Context;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use WeakMap;

use function Hyperf\Tappable\tap;

class DbConnectionAspect extends AbstractAspect
{
    public array $classes = [
        'Hyperf\Database\Connectors\Connector::createPdoConnection',
        'Hyperf\Database\Connection::getPdoForSelect',
        'Hyperf\Database\Connection::getPdo',
    ];

    private WeakMap $serverCache;

    public function __construct(protected Feature $feature)
    {
        $this->serverCache = new WeakMap();
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        return tap($proceedingJoinPoint->process(), function ($pdo) use ($proceedingJoinPoint) {
            if (! $this->feature->isTracingSpanEnabled('db')) {
                return;
            }

            if ($proceedingJoinPoint->methodName === 'createPdoConnection') {
                $dsn = $proceedingJoinPoint->arguments['keys']['dsn'] ?? '';
                $pattern = '/host=([^;]+);port=(\d+);?/';
                if (preg_match($pattern, $dsn, $matches)) {
                    $host = $matches[1];
                    $port = $matches[2];
                    $this->serverCache[$pdo] = ['host' => $host, 'port' => $port];
                }
                return;
            }

            Context::getOrSet(self::class, function () use ($pdo) {
                $server = $this->serverCache[$pdo] ?? null;

                if (is_array($server)) {
                    SentryContext::setDbServerAddress($server['host']);
                    SentryContext::setDbServerPort((int) $server['port']);
                }

                return true;
            });
        });
    }
}
