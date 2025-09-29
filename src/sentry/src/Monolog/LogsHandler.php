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
        null|int|LogLevel $logLevel = null,
        protected bool $bubble = true
    ) {
        if (is_int($logLevel)) {
            $logLevel = self::getSentryLogLevelFromMonologLevel($logLevel);
        }

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

        // Do not collect logs for exceptions, they should be handled separately by the `Handler` or `captureException`
        if (
            isset($record['context']['exception'])
            && $record['context']['exception'] instanceof Throwable
        ) {
            return false;
        }

        Logs::getInstance()->aggregator()->add(
            self::getSentryLogLevelFromMonologLevel($record['level']),
            $record['message'],
            [],
            array_merge(
                ['record.context' => $record['context']],
                ['record.extra' => $record['extra']],
                ['logger.channel' => $record['channel'] ?? ''],
                ['logger.group' => $this->group],
                ['sentry.origin' => 'auto.logger.monolog']
            )
        );

        return $this->bubble === false;
    }
}
