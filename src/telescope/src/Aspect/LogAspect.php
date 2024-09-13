<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Telescope\Aspect;

use FriendsOfHyperf\Telescope\IncomingEntry;
use FriendsOfHyperf\Telescope\Telescope;
use FriendsOfHyperf\Telescope\TelescopeConfig;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Stringable\Str;
use Monolog\Logger;
use Sentry\Monolog\CompatibilityProcessingHandlerTrait;
use UnitEnum;

use function Hyperf\Tappable\tap;

class LogAspect extends AbstractAspect
{
    use CompatibilityProcessingHandlerTrait;

    public array $classes = [
        Logger::class . '::addRecord',
    ];

    public function __construct(protected TelescopeConfig $telescopeConfig)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        return tap($proceedingJoinPoint->process(), function ($result) use ($proceedingJoinPoint) {
            if (! $this->telescopeConfig->isEnable('log')) {
                return;
            }
            $level = $proceedingJoinPoint->arguments['keys']['level'];
            $level = $level instanceof UnitEnum ? (int) $level->value : (int) $level; /* @phpstan-ignore-line */
            $message = $proceedingJoinPoint->arguments['keys']['message'];
            $context = $proceedingJoinPoint->arguments['keys']['context'];
            if (Str::contains($message, Telescope::getPath())) {
                return;
            }
            $name = $proceedingJoinPoint->getInstance()->getName();
            if ($this->telescopeConfig->isLogIgnored($name)) {
                return;
            }
            Telescope::recordLog(
                IncomingEntry::make([
                    'level' => (string) $this->getSeverityFromLevel($level),
                    'message' => $message,
                    'context' => $context,
                ])
            );
        });
    }

    /**
     * Nothing to do.
     * @param array<string, mixed>|LogRecord $record
     */
    protected function doWrite($record): void
    {
    }
}
