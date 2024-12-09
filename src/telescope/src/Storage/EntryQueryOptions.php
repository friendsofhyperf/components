<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Telescope\Storage;

use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;

class EntryQueryOptions
{
    /**
     * The batch ID that entries should belong to.
     *
     * @var string|null
     */
    public $batchId;

    /**
     * The tag that must belong to retrieved entries.
     *
     * @var string|null
     */
    public $tag;

    /**
     * The family hash that must belong to retrieved entries.
     *
     * @var string
     */
    public $familyHash;

    /**
     * The ID that all retrieved entries should be less than.
     *
     * @var mixed
     */
    public $beforeSequence;

    /**
     * The list of UUIDs of entries tor retrieve.
     *
     * @var mixed
     */
    public $uuids;

    /**
     * The number of entries to retrieve.
     *
     * @var int
     */
    public $limit = 50;

    /**
     * Create new entry query options from the incoming request.
     * @param RequestInterface $request
     */
    public static function fromRequest(ServerRequestInterface $request): static
    {
        return (new static())
            ->batchId($request->input('batch_id'))
            ->uuids($request->input('uuids'))
            ->beforeSequence($request->input('before'))
            ->tag($request->input('tag'))
            ->familyHash($request->input('family_hash'))
            ->limit((int) $request->input('take', 50));
    }

    /**
     * Create new entry query options for the given batch ID.
     */
    public static function forBatchId(?string $batchId): static
    {
        return (new static())->batchId($batchId);
    }

    /**
     * Set the batch ID for the query.
     *
     * @return $this
     */
    public function batchId(?string $batchId): static
    {
        $this->batchId = $batchId;

        return $this;
    }

    /**
     * Set the list of UUIDs of entries tor retrieve.
     *
     * @return $this
     */
    public function uuids(?array $uuids): static
    {
        $this->uuids = $uuids;

        return $this;
    }

    /**
     * Set the ID that all retrieved entries should be less than.
     *
     * @param mixed $id
     * @return $this
     */
    public function beforeSequence($id): static
    {
        $this->beforeSequence = $id;

        return $this;
    }

    /**
     * Set the tag that must belong to retrieved entries.
     *
     * @return $this
     */
    public function tag(?string $tag): static
    {
        $this->tag = $tag;

        return $this;
    }

    /**
     * Set the family hash that must belong to retrieved entries.
     *
     * @return $this
     */
    public function familyHash(?string $familyHash): static
    {
        $this->familyHash = $familyHash;

        return $this;
    }

    /**
     * Set the number of entries that should be retrieved.
     *
     * @return $this
     */
    public function limit(int $limit): static
    {
        $this->limit = $limit;

        return $this;
    }
}
