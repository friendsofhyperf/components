<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\GrpcValidation;

use FriendsOfHyperf\GrpcValidation\Annotation\ValidationAspect;

class ConfigProvider
{
    public function __invoke(): array
    {
        defined('BASE_PATH') or define('BASE_PATH', __DIR__ . '/../../../');

        return [
            // 'annotations' => [
            //     'scan' => [
            //         'paths' => [
            //             __DIR__,
            //         ],
            //     ],
            // ],
            'aspects' => [
                ValidationAspect::class,
            ],
        ];
    }
}
