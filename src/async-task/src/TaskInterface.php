<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\AsyncTask;

interface TaskInterface
{
    public function setDelay(int $delay): void;

    public function getDelay(): int;

    public function setMaxAttempts(int $maxAttempts): void;

    public function getMaxAttempts(): int;

    public function setRetryAfter(int $retryAfter): void;

    public function getRetryAfter(): int;

    public function handle(): void;
}
