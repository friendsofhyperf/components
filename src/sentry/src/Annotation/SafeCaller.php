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
class SafeCaller extends AbstractAnnotation
{
    public $exceptionHandler;

    /**
     * @param mixed $default 默认返回值
     * @param null|callable $exceptionHandler 异常处理函数，并且决定是否上报sentry
     */
    public function __construct(
        public mixed $default = null,
        ?callable $exceptionHandler = null
    ) {
        $this->exceptionHandler = $exceptionHandler;
    }
}
