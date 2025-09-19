<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Sentry\Integration;

use Hyperf\Context\Context;
use Psr\Http\Message\ServerRequestInterface;
use Sentry\Event;
use Sentry\Integration\IntegrationInterface;
use Sentry\SentrySdk;
use Sentry\State\Scope;
use Sentry\UserDataBag;

class RequestIntegration implements IntegrationInterface
{
    public function setupOnce(): void
    {
        Scope::addGlobalEventProcessor(function (Event $event): Event {
            $hub = SentrySdk::getCurrentHub();
            $self = $hub->getIntegration(self::class);

            if (! $self instanceof self || ! $hub->getClient()?->getOptions()->shouldSendDefaultPii()) {
                return $event;
            }

            $ip = $this->getClientIp();

            if (! $user = $event->getUser()) {
                $user = UserDataBag::createFromUserIpAddress($ip);
            } else {
                $user->setIpAddress($ip);
            }

            $event->setUser($user);

            return $event;
        });
    }

    protected function getClientIp(): ?string
    {
        /** @var null|ServerRequestInterface $request */
        $request = Context::get(ServerRequestInterface::class);

        if (! $request) {
            return '127.0.0.1';
        }

        return $request->getHeaderLine('x-real-ip') ?: $request->getServerParams()['remote_addr'] ?? '127.0.0.1';
    }
}
