<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Telescope\Controller;

use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ApplicationInterface;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\DeleteMapping;
use Symfony\Component\Console\Input\ArrayInput;

#[Controller(server: 'telescope')]
class EntriesController
{
    /**
     * Delete all of the entries from storage.
     */
    #[DeleteMapping(path: '/telescope/telescope-api/entries')]
    public function destroy(): void
    {
        $application = ApplicationContext::getContainer()->get(ApplicationInterface::class);
        $application->setAutoExit(false);
        $application->run(
            new ArrayInput(['command' => 'telescope:clear', '--disable-event-dispatcher' => true])
        );
    }
}
