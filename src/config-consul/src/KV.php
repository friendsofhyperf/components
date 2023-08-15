<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\ConfigConsul;

use Hyperf\Stringable\Str;

class KV
{
    public ?string $lockIndex = null;

    public ?string $key = null;

    public ?string $value = null;

    public ?string $flags = null;

    public ?int $createIndex = null;

    public ?int $modifyIndex = null;

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
        $this->createIndex = $data['CreateIndex'] ?? null;
        $this->modifyIndex = $data['ModifyIndex'] ?? null;
    }

    public function isValid()
    {
        return isset($this->value, $this->key);
    }
}
