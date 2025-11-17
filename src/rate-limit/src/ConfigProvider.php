<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\RateLimit;

use FriendsOfHyperf\RateLimit\Aspect\RateLimitAspect;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                // Add dependencies here
            ],
            'aspects' => [
                RateLimitAspect::class,
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The configuration file of rate-limit.',
                    'source' => __DIR__ . '/../publish/rate_limit.php',
                    'destination' => BASE_PATH . '/config/autoload/rate_limit.php',
                ],
            ],
        ];
    }
}
