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
use Symfony\Component\Console\Input\ArrayInput;

class EntriesController
{
    /**
     * Delete all of the entries from storage.
     */
    public function destroy(): void
    {
        $application = ApplicationContext::getContainer()->get(ApplicationInterface::class);
        $application->setAutoExit(false);
        $application->run(
            new ArrayInput(['command' => 'telescope:clear', '--disable-event-dispatcher' => true])
        );
    }
}
