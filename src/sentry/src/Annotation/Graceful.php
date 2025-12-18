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

#[Attribute(Attribute::TARGET_METHOD)]
class Graceful extends AbstractAnnotation
{
    /**
     * Convert to the specified exception and throw it.
     */
    public const STRATEGY_TRANSLATE = 'translate';

    /**
     * Call the fallback method and return.
     */
    public const STRATEGY_FALLBACK = 'fallback';

    /**
     * Swallow the exception and return null.
     */
    public const STRATEGY_SWALLOW = 'swallow';

    /**
     * Record and rethrow the original exception.
     */
    public const STRATEGY_RETHROW = 'rethrow';

    /**
     * @param null|callable $fallback Fallback method
     */
    public function __construct(
        public string $strategy = self::STRATEGY_SWALLOW,
        public $fallback = null, // Used when strategy=fallback
        public ?string $mapTo = null, // Used when strategy=translate (full exception class name)
        public bool $report = true, // Whether to log
    ) {
    }
}
