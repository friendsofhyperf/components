<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Sentry\Monolog;

use Monolog\LogRecord;
use Sentry\Logs\LogLevel;
use Sentry\Logs\Logs;
use Sentry\Monolog\CompatibilityLogLevelTrait;
use Throwable;

class LogsHandler extends \Sentry\Monolog\LogsHandler
{
    use CompatibilityLogLevelTrait;

    public function __construct(
        protected string $group = 'default',
        ?LogLevel $logLevel = null,
        protected bool $bubble = true
    ) {
        parent::__construct($logLevel, $bubble);
    }

    /**
     * @param array<string, mixed>|LogRecord $record
     */
    public function handle($record): bool
    {
        if (! $this->isHandling($record)) {
            return false;
        }
        // Do not collect logs for exceptions, they should be handled seperately by the `Handler` or `captureException`
        if (isset($record['context']['exception']) && $record['context']['exception'] instanceof Throwable) {
            return false;
        }

        Logs::getInstance()->aggregator()->add(
            self::getSentryLogLevelFromMonologLevel($record['level']),
            $record['message'],
            [],
            array_merge(
                ['log_context' => $record['context']],
                ['log_extra' => $record['extra']],
                ['logger' => $record['channel'] ?? ''],
                ['group' => $this->group]
            )
        );

        return $this->bubble === false;
    }
}
