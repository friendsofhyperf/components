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

use FriendsOfHyperf\Mail\Factory\MailerFactory;
use FriendsOfHyperf\Mail\Factory\MarkdownFactory;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                Contract\Mailer::class => MailerFactory::class,
                Markdown::class => MarkdownFactory::class,
            ],
            'publish' => [
                [
                    'id' => 'mail config',
                    'description' => 'The config for mail.',
                    'source' => __DIR__ . '/../publish/mail.php',
                    'destination' => BASE_PATH . '/config/autoload/mail.php',
                ],
                [
                    'id' => 'mail resources',
                    'description' => 'The resources for mail.',
                    'source' => __DIR__ . '/../publish/resources/',
                    'destination' => BASE_PATH . '/resources/views/',
                ],
            ],
        ];
    }
}
