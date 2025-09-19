<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Notification;

use Closure;
use FriendsOfHyperf\Notification\Event\NotificationSending;
use FriendsOfHyperf\Notification\Event\NotificationSent;
use Hyperf\Collection\Collection;
use Hyperf\Contract\TranslatorInterface;
use Hyperf\Database\Model\Collection as ModelCollection;
use Hyperf\Database\Model\Model;
use Hyperf\Stringable\Str;
use Psr\EventDispatcher\EventDispatcherInterface;

use function Hyperf\Support\value;

/**
 * @property null|string $locale
 */
class NotificationSender
{
    public function __construct(
        public ChannelManager $channelManager,
        public EventDispatcherInterface $dispatcher,
        public TranslatorInterface $translator,
    ) {
    }

    /**
     * Send the given notification immediately.
     */
    public function send(mixed $notifiables, Notification $notification, ?array $channels = null): void
    {
        $notifiables = $this->formatNotifiables($notifiables);
        $original = clone $notification;
        foreach ($notifiables as $notifiable) {
            $viaChannels = value(function ($channels, $notification, $notifiable) {
                if ($channels) {
                    return $channels;
                }

                if (method_exists($notification, 'via')) {
                    return $notification->via($notifiable);
                }
                return null;
            }, $channels, $notification, $notifiable);

            if ($viaChannels === null) {
                continue;
            }

            $this->withLocale($this->preferredLocale($notifiable, $notification), function () use ($viaChannels, $notifiable, $original) {
                $notificationId = Str::uuid()->toString();

                foreach ((array) $viaChannels as $channel) {
                    if (! ($notifiable instanceof AnonymousNotifiable && $channel === 'database')) {
                        $this->sendToNotifiable($notifiable, $notificationId, clone $original, $channel);
                    }
                }
            });
        }
    }

    /**
     * Run the callback with the given locale.
     */
    public function withLocale(?string $locale, Closure $callback): mixed
    {
        if (! $locale) {
            return $callback();
        }

        $original = $this->translator->getLocale();

        try {
            $this->translator->setLocale($locale);

            return $callback();
        } finally {
            $this->translator->setLocale($original);
        }
    }

    /**
     * Send the given notification to the given notifiable via a channel.
     */
    protected function sendToNotifiable(mixed $notifiable, string $id, Notification $notification, string $channel): void
    {
        if (! $notification->id) {
            $notification->id = $id;
        }

        if (! $this->shouldSendNotification($notifiable, $notification, $channel)) {
            return;
        }

        $response = $this->channelManager->channel($channel)->send($notifiable, $notification);

        $this->dispatcher->dispatch(
            new NotificationSent($notifiable, $notification, $channel, $response)
        );
    }

    /**
     * Get the notifiable's preferred locale for the notification.
     */
    protected function preferredLocale(mixed $notifiable, Notification $notification): ?string
    {
        return $notification->locale ?? $this->locale ?? value(function () use ($notifiable) {
            if (method_exists($notifiable, 'preferredLocale')) {
                return $notifiable->preferredLocale();
            }
            return null;
        });
    }

    /**
     * Determines if the notification can be sent.
     */
    protected function shouldSendNotification(mixed $notifiable, Notification $notification, string $channel): bool
    {
        if (method_exists($notification, 'shouldSend')
            && $notification->shouldSend($notifiable, $channel) === false) {
            return false;
        }
        $event = new NotificationSending($notifiable, $notification, $channel);
        $this->dispatcher->dispatch($event);

        return $event->shouldSend();
    }

    /**
     * Format the notifiables into a Collection / array if necessary.
     */
    protected function formatNotifiables(mixed $notifiables): Collection|array
    {
        if (! $notifiables instanceof Collection && ! is_array($notifiables)) {
            return $notifiables instanceof Model
                ? new ModelCollection([$notifiables]) : [$notifiables];
        }

        return $notifiables;
    }
}
