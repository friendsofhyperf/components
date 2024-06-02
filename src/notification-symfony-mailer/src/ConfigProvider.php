<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Notification\Symfony\Mailer;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                'symfony.email' => fn ($container) => $container->get(EmailChannel::class),
                EmailChannel::class => EmailChannelFactory::class,
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for symfony email.',
                    'source' => __DIR__ . '/../publish/email.php',
                    'destination' => BASE_PATH . '/config/autoload/symfony/email.php',
                ],
            ],
        ];
    }
}
