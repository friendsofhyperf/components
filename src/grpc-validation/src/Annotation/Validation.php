<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\GrpcValidation\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

#[Attribute(Attribute::TARGET_METHOD)]
class Validation extends AbstractAnnotation
{
    public function __construct(
        public array $rules = [],
        public array $messages = [],
        public string $formRequest = '',
        public string $scene = '',
        public bool $resolve = true
    ) {
    }
}
