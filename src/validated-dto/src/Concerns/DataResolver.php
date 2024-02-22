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
    public static function fromJson(string $json, ?string $scene = null): static
    {
        $jsonDecoded = json_decode($json, true);
        if (! is_array($jsonDecoded)) {
            throw new InvalidJsonException();
        }

        return new static($jsonDecoded, $scene);
    }

    /**
     * @throws CastTargetException|MissingCastTypeException
     */
    public static function fromArray(array $array, ?string $scene = null): static
    {
        return new static($array, $scene);
    }

    /**
     * Creates a DTO instance from a Request.
     * @param \Hyperf\HttpServer\Contract\RequestInterface $request
     * @throws ValidationException|MissingCastTypeException|CastTargetException
     */
    public static function fromRequest(RequestInterface $request, ?string $scene = null): static
    {
        return new static($request->all(), $scene);
    }

    /**
     * @throws ValidationException|MissingCastTypeException|CastTargetException
     */
    public static function fromModel(Model $model, ?string $scene = null): static
    {
        return new static($model->toArray(), $scene);
    }

    /**
     * @throws ValidationException|MissingCastTypeException|CastTargetException
     */
    public static function fromCommandArguments(Command $command, ?string $scene = null): static
    {
        $arguments = (fn () => $this->input->getArguments())->call($command);
        return new static(self::filterArguments($arguments), $scene);
    }

    /**
     * @throws ValidationException|MissingCastTypeException|CastTargetException
     */
    public static function fromCommandOptions(Command $command, ?string $scene = null): static
    {
        $options = (fn () => $this->input->getOptions())->call($command);
        return new static($options, $scene);
    }

    /**
     * @throws ValidationException|MissingCastTypeException|CastTargetException
     */
    public static function fromCommand(Command $command, ?string $scene = null): static
    {
        $arguments = (fn () => $this->input->getArguments())->call($command);
        $options = (fn () => $this->input->getOptions())->call($command);
        return new static(array_merge(self::filterArguments($arguments), $options), $scene);
    }

    private static function filterArguments(array $arguments): array
    {
        $result = [];
        foreach ($arguments as $key => $value) {
            if (! is_numeric($key)) {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}
