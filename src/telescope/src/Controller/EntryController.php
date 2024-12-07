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

use FriendsOfHyperf\Telescope\Contract\EntriesRepository;
use FriendsOfHyperf\Telescope\Storage\EntryQueryOptions;
use FriendsOfHyperf\Telescope\TelescopeConfig;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

abstract class EntryController
{
    public function __construct(
        protected ContainerInterface $container,
        protected RequestInterface $request,
        protected ResponseInterface $response,
        protected TelescopeConfig $telescopeConfig,
    ) {
    }

    public function index(RequestInterface $request, EntriesRepository $storage)
    {
        return $this->response->json([
            'entries' => $storage->get(
                $this->entryType(),
                EntryQueryOptions::fromRequest($request)
            ),
            'status' => $this->status(),
        ]);
    }

    public function show(EntriesRepository $storage, $id)
    {
        $entry = $storage->find($id)->generateAvatar();

        return $this->response->json([
            'entry' => $entry,
            'batch' => $storage->get(null, EntryQueryOptions::forBatchId($entry->batchId)->limit(-1)),
        ]);
    }

    /**
     * The entry type for the controller.
     *
     * @return string
     */
    abstract protected function entryType();

    /**
     * Determine the watcher recording status.
     */
    protected function status(): string
    {
        return $this->telescopeConfig->isRecording() ? 'enabled' : 'paused';
    }
}
