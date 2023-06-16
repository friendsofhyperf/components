<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.0/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\ReCaptcha;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [],
            'listeners' => [
                Listener\ValidatorFactoryResolvedListener::class => PHP_INT_MIN,
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The configuration file of recaptcha.',
                    'source' => __DIR__ . '/../publish/recaptcha.php',
                    'destination' => BASE_PATH . '/config/autoload/recaptcha.php',
                ],
            ],
        ];
    }
}
