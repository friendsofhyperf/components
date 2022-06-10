<?php

declare(strict_types=1);
/**
 * This file is part of grpc-validation.
 *
 * @link     https://github.com/friendofhyperf/grpc-validation
 * @document https://github.com/friendofhyperf/grpc-validation/blob/main/README.md
 * @contact  huangdijia@gmail.com
 * @license  https://github.com/friendofhyperf/grpc-validation/blob/main/LICENSE
 */
namespace FriendsOfHyperf\GrpcValidation;

class ConfigProvider
{
    public function __invoke(): array
    {
        defined('BASE_PATH') or define('BASE_PATH', __DIR__ . '/../../../');

        return [
            'dependencies' => [],
            'listeners' => [],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            'publish' => [],
        ];
    }
}
