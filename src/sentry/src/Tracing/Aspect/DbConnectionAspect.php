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

use FriendsOfHyperf\Sentry\Constants;
use Hyperf\Context\Context;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use PDO;

use function Hyperf\Tappable\tap;

class DbConnectionAspect extends AbstractAspect
{
    public array $classes = [
        'Hyperf\Database\Connection::getPdoForSelect',
        'Hyperf\Database\Connection::getPdo',
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        return tap($proceedingJoinPoint->process(), function ($pdo) use ($proceedingJoinPoint) {
            if ($proceedingJoinPoint->methodName === 'getPdoForSelect') {
                $arguments = $proceedingJoinPoint->arguments['keys'] ?? [];
                Context::set(Constants::TRACE_DB_USE_READ_PDO, $arguments['useReadPdo'] ?? false);
            }

            Context::getOrSet(self::class, function () use ($pdo) {
                $connectionStatus = $pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS);
                [$host] = explode(' ', $connectionStatus);

                Context::set(Constants::TRACE_DB_SERVER_ADDRESS, $host);

                return true;
            });
        });
    }
}
