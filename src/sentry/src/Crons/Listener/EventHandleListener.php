<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Sentry\Crons\Listener;

use FriendsOfHyperf\Sentry\Constants;
use FriendsOfHyperf\Sentry\Feature;
use Hyperf\Context\Context;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Crontab\Event;
use Hyperf\Event\Contract\ListenerInterface;
use Sentry\CheckInStatus;
use Sentry\SentrySdk;

class EventHandleListener implements ListenerInterface
{
    public function __construct(
        protected ConfigInterface $config,
        protected Feature $feature,
        protected StdoutLoggerInterface $logger
    ) {
    }

    public function listen(): array
    {
        return [
            Event\BeforeExecute::class,
            Event\AfterExecute::class,
            Event\FailToExecute::class,
        ];
    }

    /**
     * @param Event\BeforeExecute|Event\AfterExecute|Event\FailToExecute $event
     */
    public function process(object $event): void
    {
        if (! $this->feature->isCronsEnabled()) {
            return;
        }

        $options = [];
        if (method_exists($event->crontab, 'getOptions')) {
            $options = $event->crontab->getOptions();
        }

        if (isset($options['monitor']) && $options['monitor'] === false) {
            return;
        }

        match ($event::class) {
            Event\BeforeExecute::class => $this->handleCrontabTaskStarting($event, $options),
            Event\AfterExecute::class => $this->handleCrontabTaskFinished($event),
            Event\FailToExecute::class => $this->handleCrontabTaskFailed($event),
            default => null,
        };
    }

    protected function handleCrontabTaskStarting(Event\BeforeExecute $event, array $options): void
    {
        $hub = SentrySdk::getCurrentHub();
        $slug = $event->crontab->getName();
        $rule = $event->crontab->getRule();
        $rules = explode(' ', $rule);

        if (count($rules) > 5) {
            $this->logger->warning(sprintf('Crontab rule %s is not supported by sentry', $rule));
            return;
        }

        $updateMonitorConfig = (bool) ($options['update_monitor_config'] ?? true);
        $monitorConfig = null;

        if ($updateMonitorConfig) {
            $monitorConfig = $this->createMonitorConfig($event, $options, $rule);
        }

        $checkInId = $hub->captureCheckIn(
            slug: $slug,
            status: CheckInStatus::inProgress(),
            monitorConfig: $monitorConfig,
        );

        Context::set(Constants::CRON_CHECKIN_ID, $checkInId);
    }

    protected function handleCrontabTaskFinished(Event\AfterExecute $event): void
    {
        /** @var null|string $checkInId */
        $checkInId = Context::get(Constants::CRON_CHECKIN_ID);
        if (! $checkInId) {
            return;
        }

        $hub = SentrySdk::getCurrentHub();
        $slug = $event->crontab->getName();

        $hub->captureCheckIn(
            slug: $slug,
            status: CheckInStatus::ok(),
            checkInId: $checkInId,
        );
    }

    protected function handleCrontabTaskFailed(Event\FailToExecute $event): void
    {
        /** @var null|string $checkInId */
        $checkInId = Context::get(Constants::CRON_CHECKIN_ID);
        if (! $checkInId) {
            return;
        }

        $hub = SentrySdk::getCurrentHub();
        $slug = $event->crontab->getName();

        $hub->captureCheckIn(
            slug: $slug,
            status: CheckInStatus::error(),
            checkInId: $checkInId,
        );
    }

    protected function createMonitorConfig(Event\BeforeExecute $event, array $options, string $rule): \Sentry\MonitorConfig
    {
        $monitorSchedule = \Sentry\MonitorSchedule::crontab($rule);
        $checkinMargin = (int) ($options['checkin_margin'] ?? $this->config->get('sentry.crons.checkin_margin', 5));
        $maxRuntime = (int) ($options['max_runtime'] ?? $this->config->get('sentry.crons.max_runtime', 15));

        $timezone = null;
        if (method_exists($event->crontab, 'getTimezone')) {
            $timezone = $event->crontab->getTimezone();
        }
        $timezone ??= $this->config->get('sentry.crons.timezone', date_default_timezone_get());

        $failureIssueThreshold = isset($options['failure_issue_threshold']) ? (int) $options['failure_issue_threshold'] : null;
        $recoveryThreshold = isset($options['recovery_threshold']) ? (int) $options['recovery_threshold'] : null;

        return new \Sentry\MonitorConfig(
            schedule: $monitorSchedule,
            checkinMargin: $checkinMargin,
            maxRuntime: $maxRuntime,
            timezone: $timezone,
            failureIssueThreshold: $failureIssueThreshold,
            recoveryThreshold: $recoveryThreshold,
        );
    }
}
