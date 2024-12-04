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

class RecordingController
{
    public function __construct(
        protected TelescopeConfig $telescopeConfig
    ) {
    }

    /**
     * Toggle recording.
     */
    public function toggle(): void
    {
        $this->telescopeConfig->setRecording(! $this->telescopeConfig->isRecording());
    }
}
