<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Sentry\Tracing\Aspect;

use FriendsOfHyperf\Sentry\Tracing\TagManager;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;

class TagsFilterAspect extends AbstractAspect
{
    public array $classes = [
        'Sentry\Tracing\Span::setData',
        'Sentry\Tracing\Span::setTags',
        'Sentry\Tracing\SpanContext::setData',
        'Sentry\Tracing\SpanContext::setTags',
    ];

    public function __construct(private TagManager $tagManager)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        /** @var array<string, mixed> $tags */
        $tags = $proceedingJoinPoint->arguments['keys']['tags'];
        $filteredTags = [];

        foreach ($tags as $key => $value) {
            if ($this->tagManager->has($key)) {
                $filteredTags[$this->tagManager->get($key)] = $value;
            }
        }

        $proceedingJoinPoint->arguments['keys']['tags'] = $filteredTags;

        return $proceedingJoinPoint->process();
    }
}
