<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/2.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\ReCaptcha;

class ConfigProvider
{
    public function __invoke(): array
    {
        defined('BASE_PATH') or define('BASE_PATH', '');

        return [
            'dependencies' => [
                ReCaptchaManager::class => ReCaptchaManager::class,
            ],
            'listeners' => [
                Listener\ValidatorFactoryResolvedListener::class => PHP_INT_MIN,
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config of recaptcha.',
                    'source' => __DIR__ . '/../publish/recaptcha.php',
                    'destination' => BASE_PATH . '/config/autoload/recaptcha.php',
                ],
            ],
        ];
    }
}
