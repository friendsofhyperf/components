<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/1.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Lock\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Lock extends AbstractAnnotation
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $seconds = 0;

    /**
     * @var string
     */
    public $owner;

    /**
     * @var string
     */
    public $driver = 'default';

    public function __construct($value = null)
    {
        parent::__construct($value);
    }
}
