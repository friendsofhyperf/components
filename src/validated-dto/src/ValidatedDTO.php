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

use Hyperf\Contract\ValidatorInterface;
use Hyperf\Database\Model\Model;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Arr;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Validation\ValidationException;
use InvalidArgumentException;

abstract class ValidatedDTO
{
    use Traits\InteractsWithIO;

    protected array $rules = [];

    protected array $messages = [];

    protected array $attributes = [];

    protected array $scenes = [];

    protected ?array $currentRules = null;

    protected array $validatedData = [];

    /**
     * @throws ValidationException
     */
    public function __construct(protected array $data, ?string $scene = null)
    {
        $rules = $this->rules;

        if ($scene) {
            if (! isset($this->scenes[$scene])) {
                throw new InvalidArgumentException(sprintf('Scene [%s] is not defined.', $scene));
            }

            $keys = $this->scenes[$scene] ?? null;
            $rules = Arr::only($this->rules, $keys);
        }

        $validator = ApplicationContext::getContainer()
            ->get(ValidatorFactoryInterface::class)
            ->make(
                $data,
                $rules,
                $this->messages,
                $this->attributes
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

    /**
     * Defines the default values for the properties of the DTO.
     */
    protected function defaults(): array
    {
        return [];
    }
}
