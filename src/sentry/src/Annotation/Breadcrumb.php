<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Sentry\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;
use Sentry\Breadcrumb as SentryBreadcrumb;

#[Attribute(Attribute::TARGET_METHOD)]
class Breadcrumb extends AbstractAnnotation
{
    public function __construct(
        public string $level = SentryBreadcrumb::LEVEL_INFO,
        public string $type = SentryBreadcrumb::TYPE_DEFAULT,
        public string $category = 'custom',
        public ?string $message = null,
        public array $metadata = [],
        public ?float $timestamp = null
    ) {
    }
}
