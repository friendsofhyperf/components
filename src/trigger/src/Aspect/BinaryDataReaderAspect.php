<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.0/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Trigger\Aspect;

use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use MySQLReplication\BinaryDataReader\BinaryDataReader;

/**
 * @mixin BinaryDataReader
 */
class BinaryDataReaderAspect extends AbstractAspect
{
    public array $classes = [
        BinaryDataReader::class . '::readUIntBySize',
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if ($proceedingJoinPoint->arguments['keys']['size'] == BinaryDataReader::UNSIGNED_INT64_LENGTH) {
            return (fn () => (int) $this->readUInt64())->call($proceedingJoinPoint->getInstance());
        }

        return $proceedingJoinPoint->process();
    }
}
