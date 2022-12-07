<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/2.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Sentry\Aspect;

use FriendsOfHyperf\Sentry\Annotation\Breadcrumb as BreadcrumbAnnotation;
use FriendsOfHyperf\Sentry\Integration;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Sentry\Breadcrumb;

class BreadcrumbAspect extends AbstractAspect
{
    public $annotations = [
        BreadcrumbAnnotation::class,
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $startTime = microtime(true);

        return tap($proceedingJoinPoint->process(), function ($result) use ($proceedingJoinPoint, $startTime) {
            $metadata = $proceedingJoinPoint->getAnnotationMetadata();
            /** @var null|BreadcrumbAnnotation $annotation */
            $annotation = $metadata->method[BreadcrumbAnnotation::class] ?? null;

            if (! $annotation) {
                return;
            }

            $level = $this->getLevel($annotation->level);
            $type = $this->getType($annotation->type);
            $category = $annotation->category;
            $message = $annotation->message ?? sprintf('%s::%s', $proceedingJoinPoint->className, $proceedingJoinPoint->methodName);
            $metadata = array_replace($annotation->metadata, [
                'arguments' => $proceedingJoinPoint->arguments['keys'] ?? [],
                'result' => $result,
                'timeMs' => (microtime(true) - $startTime) * 1000,
            ]);

            Integration::addBreadcrumb(new Breadcrumb($level, $type, $category, $message, $metadata));
        });
    }

    protected function getLevel($level)
    {
        return [
            Breadcrumb::LEVEL_DEBUG => Breadcrumb::LEVEL_DEBUG,
            Breadcrumb::LEVEL_INFO => Breadcrumb::LEVEL_INFO,
            Breadcrumb::LEVEL_WARNING => Breadcrumb::LEVEL_WARNING,
            Breadcrumb::LEVEL_ERROR => Breadcrumb::LEVEL_ERROR,
            Breadcrumb::LEVEL_FATAL => Breadcrumb::LEVEL_FATAL,
        ][$level] ?? Breadcrumb::LEVEL_INFO;
    }

    protected function getType($type)
    {
        return [
            Breadcrumb::TYPE_DEFAULT => Breadcrumb::TYPE_DEFAULT,
            Breadcrumb::TYPE_HTTP => Breadcrumb::TYPE_HTTP,
            Breadcrumb::TYPE_USER => Breadcrumb::TYPE_USER,
            Breadcrumb::TYPE_NAVIGATION => Breadcrumb::TYPE_NAVIGATION,
            Breadcrumb::TYPE_ERROR => Breadcrumb::TYPE_ERROR,
        ][$type] ?? Breadcrumb::TYPE_DEFAULT;
    }
}
