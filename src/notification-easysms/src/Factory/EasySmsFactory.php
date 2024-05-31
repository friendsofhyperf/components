<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Notification\EasySms\Factory;

use Hyperf\Contract\ConfigInterface;
use Overtrue\EasySms\EasySms;

class EasySmsFactory
{
    public function __construct(
        private ConfigInterface $config
    ) {
    }

    public function __invoke(): EasySms
    {
        return new EasySms($this->config->get('easy_sms', []));
    }
}
