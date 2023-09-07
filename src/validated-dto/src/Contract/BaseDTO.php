<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\ValidatedDTO\Contract;

use Hyperf\Command\Command;
use Hyperf\Database\Model\Model;
use Psr\Http\Message\RequestInterface;

interface BaseDTO
{
    public static function fromJson(string $json): self;

    public static function fromArray(array $data): self;

    public static function fromRequest(RequestInterface $request): self;

    public static function fromModel(Model $model): self;

    public static function fromCommandArguments(Command $command): self;

    public static function fromCommandOptions(Command $command): self;

    public static function fromCommand(Command $command): self;

    public function toArray(): array;

    public function toJson(): string;

    public function toPrettyJson(): string;

    public function toModel(string $model): Model;
}
