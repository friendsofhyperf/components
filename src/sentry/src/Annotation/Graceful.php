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
     * 转换为指定异常再抛出.
     */
    public const STRATEGY_TRANSLATE = 'translate';

    /**
     * 调用兜底方法并返回.
     */
    public const STRATEGY_FALLBACK = 'fallback';

    /**
     * 吞掉异常并返回 null.
     */
    public const STRATEGY_SWALLOW = 'swallow';

    /**
     * 记录后仍原样抛出.
     */
    public const STRATEGY_RETHROW = 'rethrow';

    /**
     * @param null|callable $fallback 兜底方法
     */
    public function __construct(
        public string $strategy = self::STRATEGY_SWALLOW,
        public $fallback = null, // strategy=fallback 时使用
        public ?string $mapTo = null, // strategy=translate 时使用（异常类全名）
        public bool $report = true, // 是否记录日志
    ) {
    }
}
