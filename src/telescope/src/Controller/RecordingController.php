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

use FriendsOfHyperf\Telescope\TelescopeConfig;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;

#[Controller(server: 'telescope')]
class RecordingController
{
    #[Inject()]
    protected TelescopeConfig $telescopeConfig;

    /**
     * Toggle recording.
     */
    #[PostMapping(path: '/telescope/telescope-api/toggle-recording')]
    public function toggle(): void
    {
        $this->telescopeConfig->setRecording(! $this->telescopeConfig->isRecording());
    }
}
