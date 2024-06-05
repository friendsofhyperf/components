<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Mail\Contract;

use FriendsOfHyperf\Mail\Attachment;

interface Attachable
{
    /**
     * Get an attachment instance for this entity.
     */
    public function toMailAttachment(): Attachment;
}
