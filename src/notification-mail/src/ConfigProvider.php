<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Notification\Mail;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'listeners' => [
                Listener\RegisterChannelListener::class,
                Listener\RegisterViewNamespaceListener::class,
            ],
            'commands' => [
                Command\MarkdownMailCommand::class,
            ],
        ];
    }
}
