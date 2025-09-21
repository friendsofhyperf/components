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

use FriendsOfHyperf\Sentry\Integration;
use FriendsOfHyperf\Sentry\Switcher;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Sentry\Breadcrumb;

class FilesystemAspect extends AbstractAspect
{
    public $classes = [
        'League\Flysystem\*\*Adapter::directoryExists',
        'League\Flysystem\*\*Adapter::write',
        'League\Flysystem\*\*Adapter::writeStream',
        'League\Flysystem\*\*Adapter::read',
        'League\Flysystem\*\*Adapter::readStream',
        'League\Flysystem\*\*Adapter::delete',
        'League\Flysystem\*\*Adapter::deleteDirectory',
        'League\Flysystem\*\*Adapter::createDirectory',
        'League\Flysystem\*\*Adapter::setVisibility',
        'League\Flysystem\*\*Adapter::visibility',
        'League\Flysystem\*\*Adapter::mimeType',
        'League\Flysystem\*\*Adapter::lastModified',
        'League\Flysystem\*\*Adapter::fileSize',
        'League\Flysystem\*\*Adapter::listContents',
        'League\Flysystem\*\*Adapter::move',
        'League\Flysystem\*\*Adapter::copy',

        // More adapter methods can be added here
    ];

    public function __construct(
        protected Switcher $switcher
    ) {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if (! $this->switcher->isBreadcrumbEnabled('filesystem')) {
            return $proceedingJoinPoint->process();
        }

        $method = $proceedingJoinPoint->methodName;
        $arguments = $proceedingJoinPoint->arguments['keys'] ?? [];
        // See https://develop.sentry.dev/sdk/performance/span-operations/#web-server
        $op = "file.{$method}";
        $description = match ($method) {
            'move', 'copy' => sprintf(
                'from "%s" to "%s"',
                $arguments['source'] ?? '',
                $arguments['destination'] ?? ''
            ),
            'write', 'writeStream',
            'read', 'readStream',
            'setVisibility', 'visibility',
            'delete', 'deleteDirectory', 'createDirectory',
            'fileExists', 'directoryExists',
            'listContents',
            'lastModified',
            'fileSize',
            'mimeType', => $arguments['path'] ?? '',
            default => '',
        };
        $data = match ($method) {
            'move', 'copy' => [
                'from' => $arguments['source'] ?? '',
                'to' => $arguments['destination'] ?? '',
            ],
            'write', 'writeStream' => [
                'path' => $arguments['path'] ?? '',
                'config' => $arguments['config'] ?? null,
            ],
            'read', 'readStream' => [
                'path' => $arguments['path'] ?? '',
            ],
            'setVisibility' => [
                'path' => $arguments['path'] ?? '',
                'visibility' => $arguments['visibility'] ?? '',
            ],
            'visibility',
            'delete', 'deleteDirectory' => [
                'path' => $arguments['path'] ?? '',
            ],
            'createDirectory' => [
                'path' => $arguments['path'] ?? '',
                'config' => $arguments['config'] ?? null,
            ],
            'fileExists', 'directoryExists',
            'listContents',
            'lastModified',
            'fileSize',
            'mimeType' => [
                'path' => $arguments['path'] ?? '',
            ],
            default => [],
        };

        Integration::addBreadcrumb(new Breadcrumb(
            Breadcrumb::LEVEL_INFO,
            Breadcrumb::TYPE_DEFAULT,
            $op,
            $description,
            $data
        ));

        return $proceedingJoinPoint->process();
    }
}
