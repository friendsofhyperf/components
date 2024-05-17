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
use Hyperf\Contract\Arrayable;
use Hyperf\Database\Model\Model;
use Psr\Http\Message\RequestInterface;

interface BaseDTO extends Arrayable
{
    public static function fromJson(string $json): static;

    public static function fromArray(array $data): static;

    public static function fromRequest(RequestInterface $request): static;

    public static function fromModel(Model $model): static;

    public static function fromCommandArguments(Command $command): static;

    public static function fromCommandOptions(Command $command): static;

    public static function fromCommand(Command $command): static;

    public function toJson(): string;

    public function toPrettyJson(): string;

    public function toModel(string $model): Model;
}
