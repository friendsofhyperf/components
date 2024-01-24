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
use FriendsOfHyperf\Sentry\Switcher;
use Hyperf\Context\Context;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Crontab\Event;
use Hyperf\Event\Contract\ListenerInterface;
use Sentry\CheckInStatus;
use Sentry\SentrySdk;

class CronEventListener implements ListenerInterface
{
    public function __construct(
        protected ConfigInterface $config,
        protected Switcher $switcher,
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
        if (! $this->switcher->isCronsEnable()) {
            return;
        }

        $hub = SentrySdk::getCurrentHub();
        $slug = $event->crontab->getName();
        $options = [];

        if (method_exists($event->crontab, 'getOptions')) {
            $options = $event->crontab->getOptions();
        }

        if (isset($options['monitor']) && $options['monitor'] === false) {
            return;
        }

        // Notify Sentry your job is running
        if ($event instanceof Event\BeforeExecute) {
            $rule = $event->crontab->getRule();
            $rules = explode(' ', $rule);

            if (count($rules) > 5) {
                $this->logger->warning(sprintf('Crontab rule %s is not supported by sentry', $rule));
                return;
            }

            $updateMonitorConfig = (bool) ($options['update_monitor_config'] ?? true);
            $monitorConfig = null;

            if ($updateMonitorConfig) {
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

                $monitorConfig = new \Sentry\MonitorConfig(
                    schedule: $monitorSchedule,
                    checkinMargin: $checkinMargin,
                    maxRuntime: $maxRuntime,
                    timezone: $timezone,
                    failureIssueThreshold: $failureIssueThreshold,
                    recoveryThreshold: $recoveryThreshold,
                );
            }

            $checkInId = $hub->captureCheckIn(
                slug: $slug,
                status: CheckInStatus::inProgress(),
                monitorConfig: $monitorConfig,
            );

            Context::set(Constants::CRON_CHECKIN_ID, $checkInId);
        }

        // Notify Sentry your job has completed successfully
        if ($event instanceof Event\AfterExecute) {
            /** @var string $checkInId */
            $checkInId = Context::get(Constants::CRON_CHECKIN_ID);
            if (! $checkInId) {
                return;
            }
            $hub->captureCheckIn(
                slug: $slug,
                status: CheckInStatus::ok(),
                checkInId: $checkInId,
            );
        }

        // Notify Sentry your job has failed
        if ($event instanceof Event\FailToExecute) {
            /** @var string $checkInId */
            $checkInId = Context::get(Constants::CRON_CHECKIN_ID);
            if (! $checkInId) {
                return;
            }
            $hub->captureCheckIn(
                slug: $slug,
                status: CheckInStatus::error(),
                checkInId: $checkInId,
            );
        }
    }
}
