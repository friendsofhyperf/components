<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Sentry\Aspect;

use FriendsOfHyperf\Sentry\Annotation\Breadcrumb as BreadcrumbAnnotation;
use FriendsOfHyperf\Sentry\Integration;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Sentry\Breadcrumb;

use function Hyperf\Tappable\tap;

class BreadcrumbAspect extends AbstractAspect
{
    public array $annotations = [
        BreadcrumbAnnotation::class,
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $startTime = microtime(true);

        return tap($proceedingJoinPoint->process(), function ($result) use ($proceedingJoinPoint, $startTime) {
            $metadata = $proceedingJoinPoint->getAnnotationMetadata();
            /** @var BreadcrumbAnnotation|null $annotation */
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
        return match ($level) {
            Breadcrumb::LEVEL_DEBUG => Breadcrumb::LEVEL_DEBUG,
            Breadcrumb::LEVEL_INFO => Breadcrumb::LEVEL_INFO,
            Breadcrumb::LEVEL_WARNING => Breadcrumb::LEVEL_WARNING,
            Breadcrumb::LEVEL_ERROR => Breadcrumb::LEVEL_ERROR,
            Breadcrumb::LEVEL_FATAL => Breadcrumb::LEVEL_FATAL,
            default => Breadcrumb::LEVEL_INFO,
        };
    }

    protected function getType($type)
    {
        return match ($type) {
            Breadcrumb::TYPE_DEFAULT => Breadcrumb::TYPE_DEFAULT,
            Breadcrumb::TYPE_HTTP => Breadcrumb::TYPE_HTTP,
            Breadcrumb::TYPE_USER => Breadcrumb::TYPE_USER,
            Breadcrumb::TYPE_NAVIGATION => Breadcrumb::TYPE_NAVIGATION,
            Breadcrumb::TYPE_ERROR => Breadcrumb::TYPE_ERROR,
            default => Breadcrumb::TYPE_DEFAULT,
        };
    }
}
