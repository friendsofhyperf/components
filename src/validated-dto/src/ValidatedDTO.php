<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.0/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\ValidatedDTO;

use FriendsOfHyperf\ValidatedDTO\Casting\Castable;
use FriendsOfHyperf\ValidatedDTO\Exception\CastTargetException;
use FriendsOfHyperf\ValidatedDTO\Exception\MissingCastTypeException;
use Hyperf\Collection\Arr;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ValidatorInterface;
use Hyperf\Database\Model\Model;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Validation\ValidationException;
use InvalidArgumentException;

abstract class ValidatedDTO
{
    use Traits\InteractsWithIO;

    protected array $validatedData = [];

    protected bool $requireCasting = false;

    protected ?ValidatorInterface $validator = null;

    /**
     * @throws ValidationException
     */
    public function __construct(protected array $data, protected ?string $scene = null)
    {
        $this->isValidData() ? $this->passedValidation() : $this->failedValidation();
    }

    public function __get(string $name): mixed
    {
        return $this->validatedData[$name] ?? null;
    }

    /**
     * Returns the DTO validated data in array format.
     */
    public function toArray(): array
    {
        return $this->validatedData;
    }

    /**
     * Returns the DTO validated data in a JSON string format.
     */
    public function toJson(int $options = JSON_UNESCAPED_UNICODE): string
    {
        return json_encode($this->validatedData, $options);
    }

    /**
     * Returns the DTO validated data in a pretty JSON string format.
     */
    public function toPrettyJson(): string
    {
        return json_encode($this->validatedData, JSON_PRETTY_PRINT);
    }

    /**
     * Creates a new model with the DTO validated data.
     */
    public function toModel(string $model): Model
    {
        return new $model($this->validatedData);
    }

    protected function getRules()
    {
        $rules = $this->rules();

        if ($scene = $this->scene) {
            if (! $this->hasScene($scene) || ! $keys = $this->getSceneKeys($scene)) {
                throw new InvalidArgumentException(sprintf('Scene [%s] is not defined or empty.', $scene));
            }

            $rules = Arr::only($rules, $keys);
        }

        return $rules;
    }

    /**
     * Checks if the data is valid for the DTO.
     */
    protected function isValidData(): bool
    {
        $this->validator = ApplicationContext::getContainer()
            ->get(ValidatorFactoryInterface::class)
            ->make(
                $this->data,
                $this->getRules(),
                $this->messages(),
                $this->attributes()
            );

        return ! $this->validator->fails();
    }

    /**
     * Handles a passed validation attempt.
     */
    protected function passedValidation(array $rules = []): void
    {
        $this->validatedData = $this->validatedData();

        foreach ($this->defaults() as $key => $value) {
            if (! in_array($key, array_keys($rules))) {
                continue;
            }

            $this->validatedData[$key] = $value;
        }

        $casts = $this->casts();

        foreach ($this->validatedData as $key => $value) {
            if (! array_key_exists($key, $casts)) {
                if ($this->requireCasting) {
                    throw new MissingCastTypeException($key);
                }

                continue;
            }

            $formatted = $this->shouldReturnNull($key, $value)
                    ? null
                    : $this->castValue($casts[$key], $key, $value);

            $this->validatedData[$key] = $formatted;
        }
    }

    protected function validatedData(): array
    {
        return $this->validator->validated();
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

    /**
     * @throws CastTargetException
     */
    protected function castValue(mixed $cast, string $key, mixed $value): mixed
    {
        if ($cast instanceof Castable) {
            return $cast->cast($key, $value);
        }

        if (! is_callable($cast)) {
            throw new CastTargetException($key);
        }

        return $cast($key, $value);
    }

    protected function shouldReturnNull(string $key, mixed $value): bool
    {
        return is_null($value) && $this->isOptionalProperty($key);
    }

    protected function hasScene(string $scene): bool
    {
        return isset($this->scenes()[$scene]);
    }

    protected function getSceneKeys(string $scene): array
    {
        return $this->scenes()[$scene] ?? [];
    }

    /**
     * Defines the default values for the properties of the DTO.
     */
    abstract protected function rules(): array;

    /**
     * Defines the custom messages for validator errors.
     */
    protected function messages(): array
    {
        return [];
    }

    /**
     * Defines the custom attributes for validator errors.
     */
    protected function attributes(): array
    {
        return [];
    }

    protected function scenes(): array
    {
        return [];
    }

    /**
     * Defines the default values for the properties of the DTO.
     */
    protected function defaults(): array
    {
        return [];
    }

    /**
     * Defines the type casting for the properties of the DTO.
     * @return array<string,Castable>
     */
    protected function casts(): array
    {
        return [];
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
