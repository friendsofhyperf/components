<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/2.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\ConfigConsul;

use Hyperf\Utils\Str;

class KV
{
    /**
     * @var string
     */
    public $lockIndex;

    /**
     * @var string
     */
    public $key;

    /**
     * @var string
     */
    public $value;

    /**
     * @var string
     */
    public $flags;

    /**
     * @var int
     */
    public $createIndex;

    /**
     * @var int
     */
    public $modifyIndex;

    public function __construct($data)
    {
        if (isset($data['Key'])) {
            $this->key = Str::start($data['Key'], '/');
        }
        if (isset($data['Value'])) {
            $this->value = base64_decode($data['Value']);
        }
        $this->lockIndex = $data['LockIndex'] ?? null;
        $this->flags = $data['Flags'] ?? null;
        $this->createIndex = $data['createIndex'] ?? null;
        $this->modifyIndex = $data['ModifyIndex'] ?? null;
    }

    public function isValid()
    {
        return isset($this->value, $this->key);
    }
}
