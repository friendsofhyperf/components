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
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Sentry\Event;
use Sentry\Integration\IntegrationInterface;
use Sentry\SentrySdk;
use Sentry\State\Scope;
use Sentry\UserDataBag;

class RequestIpIntegration implements IntegrationInterface
{
    public function __construct(protected ContainerInterface $container)
    {
    }

    public function setupOnce(): void
    {
        Scope::addGlobalEventProcessor(function (Event $event): Event {
            $sentry = SentrySdk::getCurrentHub();
            $self = $sentry->getIntegration(self::class);

            if (! $self instanceof self || ! $sentry->getClient()?->getOptions()->shouldSendDefaultPii()) {
                return $event;
            }

            $event->setUser(
                UserDataBag::createFromUserIpAddress($this->getClientIp())
            );

            return $event;
        });
    }

    public function getClientIp(): ?string
    {
        /** @var ServerRequestInterface|null $request */
        $request = Context::get(ServerRequestInterface::class);

        if (! $request) {
            return '127.0.0.1';
        }

        return $request->getHeaderLine('x-real-ip') ?: $request->getServerParams()['remote_addr'] ?? '127.0.0.1';
    }
}
