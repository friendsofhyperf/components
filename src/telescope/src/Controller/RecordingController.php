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

use FriendsOfHyperf\Telescope\Telescope;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;

#[Controller(server: 'telescope')]
class RecordingController
{
    /**
     * Toggle recording.
     */
    #[PostMapping(path: '/telescope/telescope-api/toggle-recording')]
    public function toggle(): void
    {
        if (Telescope::getCache()->get('telescope:pause-recording')) {
            Telescope::getCache()->delete('telescope:pause-recording');
        } else {
            Telescope::getCache()->set('telescope:pause-recording', true, 30 * 86400);
        }
    }
}
