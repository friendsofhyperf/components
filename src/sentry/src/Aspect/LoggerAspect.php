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

use BackedEnum;
use FriendsOfHyperf\Sentry\Integration;
use FriendsOfHyperf\Sentry\Switcher;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Monolog\DateTimeImmutable;
use Monolog\Logger;
use Monolog\LogRecord;
use Sentry\Breadcrumb;
use Sentry\Monolog\CompatibilityProcessingHandlerTrait;

use function Hyperf\Tappable\tap;

class LoggerAspect extends AbstractAspect
{
    use CompatibilityProcessingHandlerTrait;

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
            $level = $level instanceof BackedEnum ? (int) $level->value : (int) $level;
            $message = $proceedingJoinPoint->arguments['keys']['message'];
            $context = $proceedingJoinPoint->arguments['keys']['context'];
            /** @var DateTimeImmutable|null $datetime */
            $datetime = $proceedingJoinPoint->arguments['keys']['datetime'];

            if (isset($context['no_sentry_aspect']) && $context['no_sentry_aspect'] === true) {
                return;
            }

            Integration::addBreadcrumb(new Breadcrumb(
                (string) $this->getSeverityFromLevel($level),
                Breadcrumb::TYPE_DEFAULT,
                'log.' . Logger::getLevelName($level), // @phpstan-ignore argument.type
                $message,
                $context,
                $datetime?->getTimestamp()
            ));
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
