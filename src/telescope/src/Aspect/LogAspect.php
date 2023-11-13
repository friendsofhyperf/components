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
use FriendsOfHyperf\Telescope\Severity;
use FriendsOfHyperf\Telescope\SwitchManager;
use FriendsOfHyperf\Telescope\Telescope;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Stringable\Str;
use Monolog\Logger;
use UnitEnum;

use function Hyperf\Tappable\tap;

class LogAspect extends AbstractAspect
{
    public array $classes = [
        Logger::class . '::addRecord',
    ];

    public function __construct(protected SwitchManager $switcherManager)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        return tap($proceedingJoinPoint->process(), function ($result) use ($proceedingJoinPoint) {
            if (! $this->switcherManager->isEnable('log')) {
                return;
            }
            $level = $proceedingJoinPoint->arguments['keys']['level'];
            $level = $level instanceof UnitEnum ? (int) $level->value : (int) $level;
            $message = $proceedingJoinPoint->arguments['keys']['message'];
            $context = $proceedingJoinPoint->arguments['keys']['context'];
            if (Str::contains($message, 'telescope')) {
                return;
            }
            if ($proceedingJoinPoint->getInstance()->getName() == 'sql') {
                return;
            }
            Telescope::recordLog(
                IncomingEntry::make([
                    'level' => (string) $this->getLogLevel($level),
                    'message' => Telescope::getAppName() . $message,
                    'context' => $context,
                ])
            );
        });
    }

    /**
     * Translates Monolog log levels to Sentry Severity.
     */
    protected function getLogLevel(int $logLevel): Severity
    {
        return match ($logLevel) {
            Logger::DEBUG => Severity::debug(),
            Logger::NOTICE, Logger::INFO => Severity::info(),
            Logger::WARNING => Severity::warning(),
            Logger::ALERT, Logger::EMERGENCY, Logger::CRITICAL => Severity::fatal(),
            Logger::ERROR => Severity::error(),
            default => Severity::error(),
        };
    }
}
