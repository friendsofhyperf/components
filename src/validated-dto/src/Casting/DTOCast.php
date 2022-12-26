<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\ValidatedDTO\Casting;

use FriendsOfHyperf\ValidatedDTO\Exception\CastException;
use FriendsOfHyperf\ValidatedDTO\Exception\CastTargetException;
use FriendsOfHyperf\ValidatedDTO\ValidatedDTO;
use Hyperf\Validation\ValidationException;
use Throwable;

class DTOCast implements Castable
{
    public function __construct(private string $dtoClass)
    {
    }

    /**
     * @throws CastException|CastTargetException|ValidationException
     */
    public function cast(string $property, mixed $value): ValidatedDTO
    {
        if (is_string($value)) {
            $value = json_decode($value, true);
        }

        if (! is_array($value)) {
            throw new CastException($property);
        }

        try {
            $dto = new $this->dtoClass($value);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable) {
            throw new CastException($property);
        }

        if (! $dto instanceof ValidatedDTO) {
            throw new CastTargetException($property);
        }

        return $dto;
    }
}
