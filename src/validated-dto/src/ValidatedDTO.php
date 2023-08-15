<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\ValidatedDTO;

use FriendsOfHyperf\ValidatedDTO\Casting\Castable;
use FriendsOfHyperf\ValidatedDTO\Exception\CastTargetException;
use FriendsOfHyperf\ValidatedDTO\Exception\MissingCastTypeException;
use Hyperf\Collection\Arr;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ValidatorInterface;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Validation\ValidationException;
use InvalidArgumentException;

abstract class ValidatedDTO extends SimpleDTO
{
    protected ?ValidatorInterface $validator = null;

    public function __construct(?array $data = null, protected ?string $scene = null)
    {
        parent::__construct($data);
    }

    /**
     * Defines the custom messages for validator errors.
     */
    public function messages(): array
    {
        return [];
    }

    /**
     * Defines the custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [];
    }

    protected function scenes(): array
    {
        return [];
    }

    protected function hasScene(string $scene): bool
    {
        return isset($this->scenes()[$scene]);
    }

    protected function getSceneKeys(string $scene): array
    {
        return $this->scenes()[$scene] ?? [];
    }

    protected function getRules(): array
    {
        $rules = $this->rules();
        $scene = $this->scene;

        if ($scene) {
            if (! $this->hasScene($scene) || ! $keys = $this->getSceneKeys($scene)) {
                throw new InvalidArgumentException(sprintf('Scene [%s] is not defined or empty.', $scene));
            }

            $rules = Arr::only($rules, $keys);
        }

        return $rules;
    }

    /**
     * Defines the validation rules for the DTO.
     */
    abstract protected function rules(): array;

    /**
     * Builds the validated data from the given data and the rules.
     *
     * @throws MissingCastTypeException|CastTargetException
     */
    protected function validatedData(): array
    {
        $acceptedKeys = array_keys($this->getRules());
        $result = [];

        /** @var array<Castable> $casts */
        $casts = $this->casts();

        foreach ($this->data as $key => $value) {
            if (in_array($key, $acceptedKeys)) {
                if (! array_key_exists($key, $casts)) {
                    if ($this->requireCasting) {
                        throw new MissingCastTypeException($key);
                    }
                    $result[$key] = $value;

                    continue;
                }

                $result[$key] = $this->shouldReturnNull($key, $value)
                    ? null
                    : $this->castValue($casts[$key], $key, $value);
            }
        }

        foreach ($acceptedKeys as $property) {
            if (
                ! array_key_exists($property, $result)
                && $this->isOptionalProperty($property)
            ) {
                $result[$property] = null;
            }
        }

        return $result;
    }

    protected function isValidData(): bool
    {
        $container = ApplicationContext::getContainer();
        $this->validator = $container->get(ValidatorFactoryInterface::class)
            ->make(
                $this->data,
                $this->getRules(),
                $this->messages(),
                $this->attributes()
            );

        return ! $this->validator->fails();
    }

    /**
     * Handles a failed validation attempt.
     *
     * @throws ValidationException
     */
    protected function failedValidation(): void
    {
        throw new ValidationException($this->validator);
    }

    protected function shouldReturnNull(string $key, mixed $value): bool
    {
        return is_null($value) && $this->isOptionalProperty($key);
    }

    private function isOptionalProperty(string $property): bool
    {
        $rules = $this->getRules();
        $propertyRules = is_array($rules[$property])
            ? $rules[$property]
            : explode('|', $rules[$property]);

        return in_array('optional', $propertyRules) || in_array('nullable', $propertyRules);
    }
}
