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

use Hyperf\Collection\Arr;
use Monolog\LogRecord;
use Override;
use Sentry\Logs\LogLevel;
use Sentry\Monolog\CompatibilityLogLevelTrait;

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
     * @param array<string,mixed>|LogRecord $record
     *
     * @return array<string,mixed>
     */
    #[Override]
    protected function compileAttributes($record): array
    {
        return array_merge(
            Arr::dot($record['context'] ?? [], 'context.'),
            Arr::dot($record['extra'] ?? [], 'extra.'),
            ['logger.channel' => $record['channel'] ?? ''],
            ['logger.group' => $this->group],
            ['sentry.origin' => 'auto.log.monolog']
        );
    }
}
