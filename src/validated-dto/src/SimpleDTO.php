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

use FriendsOfHyperf\ValidatedDTO\Casting\ArrayCast;
use FriendsOfHyperf\ValidatedDTO\Casting\Castable;
use FriendsOfHyperf\ValidatedDTO\Exception\CastTargetException;
use FriendsOfHyperf\ValidatedDTO\Exception\MissingCastTypeException;
use Hyperf\Collection\Collection;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\CastsAttributes;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Database\Model\Model;
use Hyperf\Validation\ValidationException;

abstract class SimpleDTO implements CastsAttributes
{
    use Traits\InteractsWithIO;

    protected array $data = [];

    protected array $validatedData = [];

    protected bool $requireCasting = false;

    /**
     * @throws ValidationException|MissingCastTypeException|CastTargetException
     */
    public function __construct(?array $data = null)
    {
        if (is_null($data)) {
            return;
        }

        $this->data = $this->buildDataForValidation($data);

        $this->initConfig();

        $this->isValidData()
            ? $this->passedValidation()
            : $this->failedValidation();
    }

    public function __set(string $name, mixed $value): void
    {
        $this->{$name} = $value;
    }

    public function __get(string $name): mixed
    {
        return $this->{$name} ?? null;
    }

    /**
     * Returns the DTO validated data in array format.
     */
    public function toArray(): array
    {
        return $this->buildDataForExport();
    }

    /**
     * Returns the DTO validated data in a JSON string format.
     */
    public function toJson(int|bool $flags = 0, int $depth = 512): string
    {
        // compat with the flags is bool type
        $flags = (int) match ($flags) {
            true => JSON_PRETTY_PRINT,
            false => 0,
            default => $flags,
        };

        return json_encode($this->buildDataForExport(), $flags, $depth);
    }

    /**
     * Returns the DTO validated data in a pretty JSON string format.
     */
    public function toPrettyJson(int $flags = 0, int $depth = 512): string
    {
        return $this->toJson($flags | JSON_PRETTY_PRINT, $depth);
    }

    /**
     * Creates a new model with the DTO validated data.
     * @template T of \Hyperf\Database\Model\Model
     * @param class-string<T> $model
     * @return T
     */
    public function toModel(string $model): Model
    {
        return new $model($this->buildDataForExport());
    }

    /**
     * Cast the given value to a DTO instance.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     * @return $this
     *
     * @throws ValidationException|MissingCastTypeException|CastTargetException
     */
    public function get($model, $key, $value, $attributes)
    {
        $arrayCast = new ArrayCast();

        return new static($arrayCast->cast($key, $value));
    }

    /**
     * Prepare the value for storage.
     *
     * @param \Hyperf\Database\Model\Model $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     * @return string
     */
    public function set($model, $key, $value, $attributes)
    {
        if (is_string($value)) {
            return $value;
        }
        if (is_array($value)) {
            return json_encode($value);
        }
        if ($value instanceof ValidatedDTO) {
            return $value->toJson();
        }

        return '';
    }

    /**
     * Defines the default values for the properties of the DTO.
     */
    abstract protected function defaults(): array;

    /**
     * Defines the type casting for the properties of the DTO.
     */
    abstract protected function casts(): array;

    /**
     * Maps the DTO properties before the DTO instantiation.
     */
    protected function mapBeforeValidation(): array
    {
        return [];
    }

    /**
     * Maps the DTO properties before the DTO export.
     */
    protected function mapBeforeExport(): array
    {
        return [];
    }

    /**
     * Handles a passed validation attempt.
     *
     * @throws MissingCastTypeException|CastTargetException
     */
    protected function passedValidation(): void
    {
        $this->validatedData = $this->validatedData();
        /** @var array<Castable> $casts */
        $casts = $this->casts();

        foreach ($this->validatedData as $key => $value) {
            $this->{$key} = $value;
        }

        foreach ($this->defaults() as $key => $value) {
            if (
                ! property_exists($this, $key)
                || empty($this->{$key})
            ) {
                if (! array_key_exists($key, $casts)) {
                    if ($this->requireCasting) {
                        throw new MissingCastTypeException($key);
                    }

                    $this->{$key} = $value;
                    $this->validatedData[$key] = $value;

                    continue;
                }

                $formatted = $this->shouldReturnNull($key, $value)
                    ? null
                    : $this->castValue($casts[$key], $key, $value);
                $this->{$key} = $formatted;
                $this->validatedData[$key] = $formatted;
            }
        }
    }

    /**
     * Handles a failed validation attempt.
     */
    protected function failedValidation(): void
    {
        // Do nothing
    }

    /**
     * Checks if the data is valid for the DTO.
     */
    protected function isValidData(): bool
    {
        return true;
    }

    /**
     * Builds the validated data from the given data and the rules.
     *
     * @throws MissingCastTypeException|CastTargetException
     */
    protected function validatedData(): array
    {
        $acceptedKeys = $this->getAcceptedProperties();
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
            if (! array_key_exists($property, $result)) {
                $this->{$property} = null;
            }
        }

        return $result;
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
        return is_null($value);
    }

    private function buildDataForValidation(array $data): array
    {
        return $this->mapData($this->mapBeforeValidation(), $data);
    }

    private function buildDataForExport(): array
    {
        return $this->mapData($this->mapBeforeExport(), $this->validatedData);
    }

    private function mapData(array $mapping, array $data): array
    {
        if (empty($mapping)) {
            return $data;
        }

        $mappedData = [];
        foreach ($data as $key => $value) {
            $properties = $this->getMappedProperties($mapping, $key);
            if ($properties !== [] && $this->isArrayable($value)) {
                $formatted = $this->formatArrayableValue($value);

                foreach ($properties as $property => $mappedValue) {
                    $mappedData[$mappedValue] = $formatted[$property] ?? null;
                }

                continue;
            }

            $property = array_key_exists($key, $mapping)
                ? $mapping[$key]
                : $key;

            $mappedData[$property] = $value;
        }

        return $mappedData;
    }

    private function getMappedProperties(array $mapping, string $key): array
    {
        $properties = [];
        foreach ($mapping as $mappedKey => $mappedValue) {
            if (str_starts_with($mappedKey, "{$key}.")) {
                $arrayKey = str_replace("{$key}.", '', $mappedKey);
                $properties[$arrayKey] = $mappedValue;
            }

            if (str_starts_with($mappedValue, "{$key}.")) {
                $arrayKey = str_replace("{$key}.", '', $mappedValue);
                $properties[$arrayKey] = $mappedKey;
            }
        }

        return $properties;
    }

    private function isArrayable(mixed $value): bool
    {
        return is_array($value)
            || $value instanceof Collection
            || $value instanceof ValidatedDTO
            || $value instanceof Model
            || is_object($value);
    }

    private function formatArrayableValue(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_object($value)) {
            return (array) $value;
        }

        return $value->toArray();
    }

    /**
     * Inits the configuration for the DTOs.
     */
    private function initConfig(): void
    {
        $config = null;

        if (
            ApplicationContext::hasContainer()
            && $container = ApplicationContext::getContainer()
        ) {
            $config = $container->get(ConfigInterface::class)?->get('dto');
        }

        if (! empty($config) && array_key_exists('require_casting', $config)) {
            $this->requireCasting = $config['require_casting'];
        }
    }

    private function getAcceptedProperties(): array
    {
        $acceptedKeys = [];
        foreach (get_class_vars($this::class) as $key => $value) {
            if (! $this->isforbiddenProperty($key)) {
                $acceptedKeys[] = $key;
            }
        }

        return $acceptedKeys;
    }

    private function isforbiddenProperty(string $property): bool
    {
        return in_array($property, [
            'data',
            'validatedData',
            'requireCasting',
            'validator',
        ]);
    }
}
