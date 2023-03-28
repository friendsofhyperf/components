<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\ValidatedDTO;

use FriendsOfHyperf\ValidatedDTO\Casting\Castable;
use FriendsOfHyperf\ValidatedDTO\Exception\CastTargetException;
use FriendsOfHyperf\ValidatedDTO\Exception\MissingCastTypeException;
use Hyperf\Collection\Arr;
use Hyperf\Contract\ValidatorInterface;
use Hyperf\Database\Model\Model;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Validation\ValidationException;
use InvalidArgumentException;

abstract class ValidatedDTO
{
    use Traits\InteractsWithIO;

    protected array $validatedData = [];

    protected bool $requireCasting = false;

    /**
     * @throws ValidationException
     */
    public function __construct(protected array $data, ?string $scene = null)
    {
        $rules = $this->rules();

        if ($scene) {
            if (! $this->hasScene($scene) || ! $keys = $this->getSceneKeys($scene)) {
                throw new InvalidArgumentException(sprintf('Scene [%s] is not defined or empty.', $scene));
            }

            $rules = Arr::only($rules, $keys);
        }

        $validator = ApplicationContext::getContainer()
            ->get(ValidatorFactoryInterface::class)
            ->make(
                $data,
                $rules,
                $this->messages(),
                $this->attributes()
            );

        ! $validator->fails() ? $this->passedValidation($validator, $rules) : $this->failedValidation($validator);
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
     * Creates a new model with the DTO validated data.
     */
    public function toModel(string $model): Model
    {
        return new $model($this->validatedData);
    }

    /**
     * Handles a passed validation attempt.
     */
    protected function passedValidation(ValidatorInterface $validator, array $rules = []): void
    {
        $this->validatedData = $validator->validated();

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

            if (! $casts[$key] instanceof Castable) {
                throw new CastTargetException($key);
            }

            $formatted = $casts[$key]->cast($key, $value);
            $this->validatedData[$key] = $formatted;
        }
    }

    /**
     * Handles a failed validation attempt.
     *
     * @throws ValidationException
     */
    protected function failedValidation(ValidatorInterface $validator): void
    {
        throw new ValidationException($validator);
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
}
