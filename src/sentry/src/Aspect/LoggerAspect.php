<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/2.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Sentry\Aspect;

use FriendsOfHyperf\Sentry\Integration;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Monolog\DateTimeImmutable;
use Monolog\Logger;
use Sentry\Breadcrumb;
use Sentry\Severity;

class LoggerAspect extends AbstractAspect
{
    public $classes = [
        Logger::class . '::addRecord',
    ];

    /**
     * @var ConfigInterface
     */
    protected $config;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        return tap($proceedingJoinPoint->process(), function ($result) use ($proceedingJoinPoint) {
            if (! $this->config->get('sentry.breadcrumbs.logs', false)) {
                return;
            }

            $level = $proceedingJoinPoint->arguments['keys']['level'];
            $level = (int) $level;
            $message = $proceedingJoinPoint->arguments['keys']['message'];
            $context = $proceedingJoinPoint->arguments['keys']['context'];
            /** @var null|DateTimeImmutable $datetime */
            $datetime = $proceedingJoinPoint->arguments['keys']['datetime'] ?? null;

            if (isset($context['no_aspect']) && $context['no_aspect'] === true) {
                return;
            }

            Integration::addBreadcrumb(new Breadcrumb(
                (string) $this->getLogLevel($level),
                Breadcrumb::TYPE_DEFAULT,
                'log.' . Logger::getLevelName($level),
                $message,
                $context,
                optional($datetime)->getTimestamp()
            ));
        });
    }

    /**
     * Translates Monolog log levels to Sentry Severity.
     */
    protected function getLogLevel(int $logLevel): Severity
    {
        return [
            Logger::DEBUG => Severity::debug(),
            Logger::NOTICE => Severity::info(),
            Logger::INFO => Severity::info(),
            Logger::WARNING => Severity::warning(),
            Logger::ALERT => Severity::fatal(),
            Logger::EMERGENCY => Severity::fatal(),
            Logger::CRITICAL => Severity::fatal(),
            Logger::ERROR => Severity::error(),
        ][$logLevel] ?? Severity::error();
    }
}
