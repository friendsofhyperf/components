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
use Sentry\Integration\RequestFetcherInterface;

class RequestFetcher implements RequestFetcherInterface
{
    public function fetchRequest(): ?ServerRequestInterface
    {
        /** @var ServerRequestInterface|null $request */
        $request = Context::get(ServerRequestInterface::class);

        if (! $request || ! method_exists($request, 'withServerParams')) {
            return $request;
        }

        return $request->withServerParams(array_merge(
            $request->getServerParams(),
            array_change_key_case($request->getServerParams(), CASE_UPPER)
        ));
    }
}
