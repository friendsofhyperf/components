<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Sentry\Aspect;

use FriendsOfHyperf\Sentry\Integration;
use FriendsOfHyperf\Sentry\Switcher;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Monolog\DateTimeImmutable;
use Monolog\Logger;
use Sentry\Breadcrumb;
use Sentry\Severity;
use UnitEnum;

use function Hyperf\Tappable\tap;

class LoggerAspect extends AbstractAspect
{
    public array $classes = [
        Logger::class . '::addRecord',
    ];

    public function __construct(protected Switcher $switcher)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        return tap($proceedingJoinPoint->process(), function ($result) use ($proceedingJoinPoint) {
            if (! $this->switcher->isBreadcrumbEnable('logs')) {
                return;
            }

            $level = $proceedingJoinPoint->arguments['keys']['level'];
            $level = $level instanceof UnitEnum ? (int) $level->value : (int) $level; /* @phpstan-ignore-line */
            $message = $proceedingJoinPoint->arguments['keys']['message'];
            $context = $proceedingJoinPoint->arguments['keys']['context'];
            /** @var DateTimeImmutable|null $datetime */
            $datetime = $proceedingJoinPoint->arguments['keys']['datetime'];

            if (isset($context['no_sentry_aspect']) && $context['no_sentry_aspect'] === true) {
                return;
            }

            Integration::addBreadcrumb(new Breadcrumb(
                (string) $this->getLogLevel($level),
                Breadcrumb::TYPE_DEFAULT,
                'log.' . Logger::getLevelName($level), /* @phpstan-ignore-line */
                $message,
                $context,
                $datetime?->getTimestamp()
            ));
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
