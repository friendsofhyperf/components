<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\ClosureCommand\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractMultipleAnnotation;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class Command extends AbstractMultipleAnnotation
{
    public function __construct(public string $signature = '', public string $description = '', public ?string $handle = null)
    {
    }

    public function collectMethod(string $className, ?string $target): void
    {
        CommandCollector::set($className . '@' . $target, [
            'class' => $className,
            'method' => $target,
            'signature' => $this->signature,
            'description' => $this->description,
        ]);
    }

    public function collectClass(string $className): void
    {
        $target = $this->handle ?? 'handle';

        $this->collectMethod($className, $target);
    }
}
