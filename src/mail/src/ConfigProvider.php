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

use Hyperf\ViewEngine\Contract\FactoryInterface;
use Psr\Container\ContainerInterface;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                'mail.manager' => MailManager::class,
                'mailer' => fn (ContainerInterface $container) => $container->get('mail.manager')->mailer(),
                Markdown::class => fn (ContainerInterface $container) => new Markdown($container->get(FactoryInterface::class), [
                    'theme' => $container->get('config')->get('mail.markdown.theme', 'default'),
                    'paths' => $container->get('config')->get('mail.markdown.paths', []),
                ]),
            ],
            'publish' => [
                [
                    'id' => 'mail config',
                    'description' => 'The config for mail.',
                    'source' => __DIR__ . '/../publish/mail.php',
                    'destination' => BASE_PATH . '/config/autoload/mail.php',
                ],
            ],
        ];
    }
}
