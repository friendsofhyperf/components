<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Sentry;

use FriendsOfHyperf\Sentry\Tracing\TagManager;
use Hyperf\Context\ApplicationContext;
use Sentry\Tracing\Span;
use Sentry\Tracing\SpanContext;

function setTag(Span|SpanContext $span, string $key, mixed $value): void
{
    setTags($span, [$key => $value]);
}

/**
 * @param array<string, mixed> $tags
 */
function setTags(Span|SpanContext $span, array $tags = []): void
{
    $tagManager = ApplicationContext::getContainer()->get(TagManager::class);
    $filtered = [];

    foreach ($tags as $key => $value) {
        if (! $tagManager->has($key)) {
            continue;
        }
        $filtered[$tagManager->get($key)] = $value;
    }

    if ($span instanceof SpanContext) {
        $filtered = array_merge($span->getTags(), $filtered);
    }

    $span->setTags($filtered);
}

/**
 * @param array<string, mixed> $data
 */
function setData(Span|SpanContext $span, array $data = []): void
{
    $tagManager = ApplicationContext::getContainer()->get(TagManager::class);
    $filtered = [];

    foreach ($data as $key => $value) {
        if (! $tagManager->has($key)) {
            continue;
        }
        $filtered[$tagManager->get($key)] = $value;
    }

    if ($span instanceof SpanContext) {
        $filtered = array_merge($span->getData(), $filtered);
    }

    $span->setData($filtered);
}
