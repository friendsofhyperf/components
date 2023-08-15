<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\ValidatedDTO\Traits;

use FriendsOfHyperf\ValidatedDTO\Exception\InvalidJsonException;
use Hyperf\Command\Command;
use Hyperf\Database\Model\Model;
use Psr\Http\Message\RequestInterface;

trait InteractsWithIO
{
    /**
     * Creates a DTO instance from a valid array.
     *
     * @throws InvalidJsonException|ValidationException
     */
    public static function fromArray(array $data, ?string $scene = null): static
    {
        return new static($data, $scene);
    }

    /**
     * Creates a DTO instance from a valid JSON string.
     *
     * @throws InvalidJsonException|ValidationException
     */
    public static function fromJson(string $json, ?string $scene = null): static
    {
        $jsonDecoded = json_decode($json, true);

        if (! is_array($jsonDecoded)) {
            throw new InvalidJsonException();
        }

        return new static($jsonDecoded, $scene);
    }

    /**
     * Creates a DTO instance from a Request.
     * @param \Hyperf\HttpServer\Contract\RequestInterface $request
     * @throws ValidationException
     */
    public static function fromRequest(RequestInterface $request, ?string $scene = null): static
    {
        return new static($request->all(), $scene);
    }

    /**
     * Creates a DTO instance from the given model.
     *
     * @throws ValidationException
     */
    public static function fromModel(Model $model, ?string $scene = null): static
    {
        return new static($model->toArray(), $scene);
    }

    /**
     * Creates a DTO instance from the given command arguments.
     *
     * @throws ValidationException
     */
    public static function fromCommandArguments(Command $command, ?string $scene = null): static
    {
        return new static((fn () => $this->input->getArguments())->call($command), $scene);
    }

    /**
     * Creates a DTO instance from the given command options.
     *
     * @throws ValidationException
     */
    public static function fromCommandOptions(Command $command, ?string $scene = null): static
    {
        return new static((fn () => $this->input->getOptions())->call($command), $scene);
    }

    /**
     * Creates a DTO instance from the given command arguments and options.
     *
     * @throws ValidationException
     */
    public static function fromCommand(Command $command, ?string $scene = null): static
    {
        return new static(array_merge(
            (fn () => $this->input->getArguments())->call($command),
            (fn () => $this->input->getOptions())->call($command)
        ), $scene);
    }
}
