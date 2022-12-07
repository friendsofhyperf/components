<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/2.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Sentry\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;
use Sentry\Breadcrumb as SentryBreadcrumb;

/**
 * @Annotation
 * @Target("METHOD")
 */
#[Attribute(Attribute::TARGET_METHOD)]
class Breadcrumb extends AbstractAnnotation
{
    /**
     * @var string
     */
    public $level = SentryBreadcrumb::LEVEL_INFO;

    /**
     * @var string
     */
    public $type = SentryBreadcrumb::TYPE_DEFAULT;

    /**
     * @var string
     */
    public $category = 'custom';

    /**
     * @var null|string
     */
    public $message;

    /**
     * @var array
     */
    public $metadata = [];

    /**
     * @var null|float
     */
    public $timestamp;

    public function __construct(
        string $level = SentryBreadcrumb::LEVEL_INFO,
        string $type = SentryBreadcrumb::TYPE_DEFAULT,
        string $category = 'custom',
        string $message = null,
        array $metadata = [],
        float $timestamp = null
    ) {
        $this->level = $level;
        $this->type = $type;
        $this->category = $category;
        $this->message = $message;
        $this->metadata = $metadata;
        $this->timestamp = $timestamp;
    }
}
