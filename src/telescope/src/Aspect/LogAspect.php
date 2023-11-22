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
use FriendsOfHyperf\Telescope\SwitchManager;
use FriendsOfHyperf\Telescope\Telescope;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Stringable\Str;
use Monolog\Level;
use Monolog\Logger;
use UnitEnum;

use function Hyperf\Config\config;
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
            $name = $proceedingJoinPoint->getInstance()->getName();
            $ignoreLogs = config('telescope.ignore_logs', []);
            if ($ignoreLogs && in_array($name, $ignoreLogs)) {
                return;
            }
            Telescope::recordLog(
                IncomingEntry::make([
                    'level' => $this->getLogLevel($level),
                    'message' => $message,
                    'context' => $context,
                ])
            );
        });
    }

    /**
     * Translates Monolog log levels.
     */
    protected function getLogLevel(int $level): string
    {
        $logLevel = match (Level::fromValue($level)) {
            Level::Debug => Level::Debug,
            Level::Info => Level::Info,
            Level::Notice => Level::Notice,
            Level::Error => Level::Error,
            Level::Critical => Level::Critical,
            Level::Alert => Level::Alert,
            Level::Emergency => Level::Emergency,
            default => Level::Error,
        };
        return strtolower($logLevel->getName());
    }
}
