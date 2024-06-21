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

use Hyperf\Contract\ContainerInterface;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;
use Psr\SimpleCache\CacheInterface;

#[Controller(server: 'telescope')]
class RecordingController
{
    private CacheInterface $cache;

    public function __construct(ContainerInterface $container)
    {
        $this->cache = $container->get(CacheInterface::class);
    }

    /**
     * Toggle recording.
     */
    #[PostMapping(path: '/telescope/telescope-api/toggle-recording')]
    public function toggle(): void
    {
        if ($this->cache->get('telescope:pause-recording')) {
            $this->cache->delete('telescope:pause-recording');
        } else {
            $this->cache->set('telescope:pause-recording', true, 30 * 86400);
        }
    }
}
