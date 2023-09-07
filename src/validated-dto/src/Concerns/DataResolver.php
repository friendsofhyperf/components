<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\ValidatedDTO\Concerns;

use FriendsOfHyperf\ValidatedDTO\Exception\CastTargetException;
use FriendsOfHyperf\ValidatedDTO\Exception\InvalidJsonException;
use FriendsOfHyperf\ValidatedDTO\Exception\MissingCastTypeException;
use Hyperf\Command\Command;
use Hyperf\Database\Model\Model;
use Hyperf\Validation\ValidationException;
use Psr\Http\Message\RequestInterface;

trait DataResolver
{
    /**
     * @throws InvalidJsonException|ValidationException|MissingCastTypeException|CastTargetException
     */
    public static function fromJson(string $json): self
    {
        $jsonDecoded = json_decode($json, true);
        if (! is_array($jsonDecoded)) {
            throw new InvalidJsonException();
        }

        return new static($jsonDecoded);
    }

    /**
     * @throws CastTargetException|MissingCastTypeException
     */
    public static function fromArray(array $array): self
    {
        return new static($array);
    }

    /**
     * Creates a DTO instance from a Request.
     * @param \Hyperf\HttpServer\Contract\RequestInterface $request
     * @throws ValidationException|MissingCastTypeException|CastTargetException
     */
    public static function fromRequest(RequestInterface $request): self
    {
        return new static($request->all());
    }

    /**
     * @throws ValidationException|MissingCastTypeException|CastTargetException
     */
    public static function fromModel(Model $model): self
    {
        return new static($model->toArray());
    }

    /**
     * @throws ValidationException|MissingCastTypeException|CastTargetException
     */
    public static function fromCommandArguments(Command $command): self
    {
        $arguments = (fn () => $this->input->getArguments())->call($command);
        return new static($arguments);
    }

    /**
     * @throws ValidationException|MissingCastTypeException|CastTargetException
     */
    public static function fromCommandOptions(Command $command): self
    {
        $options = (fn () => $this->input->getOptions())->call($command);
        return new static($options);
    }

    /**
     * @throws ValidationException|MissingCastTypeException|CastTargetException
     */
    public static function fromCommand(Command $command): self
    {
        $arguments = (fn () => $this->input->getArguments())->call($command);
        $options = (fn () => $this->input->getOptions())->call($command);
        return new static(array_merge($arguments, $options));
    }
}
