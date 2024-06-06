<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Mail;

use Psr\Container\ContainerInterface;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                Contract\Mailer::class => Factory\MailerFactory::class,
                Markdown::class => Factory\MarkdownFactory::class,
                Contract\Factory::class => fn (ContainerInterface $container) => $container->get(MailManager::class),
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for mail.',
                    'source' => __DIR__ . '/../publish/mail.php',
                    'destination' => BASE_PATH . '/config/autoload/mail.php',
                ],
                [
                    'id' => 'resources',
                    'description' => 'The resources for mail.',
                    'source' => __DIR__ . '/../publish/resources/views/',
                    'destination' => BASE_PATH . '/storage/view/mail/',
                ],
            ],
            'commands' => [
                Command\MailCommand::class,
            ],
        ];
    }
}
