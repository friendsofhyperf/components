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

use BackedEnum;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use FriendsOfHyperf\ValidatedDTO\Attributes\Cast;
use FriendsOfHyperf\ValidatedDTO\Attributes\DefaultValue;
use FriendsOfHyperf\ValidatedDTO\Attributes\Map;
use FriendsOfHyperf\ValidatedDTO\Attributes\Rules;
use FriendsOfHyperf\ValidatedDTO\Casting\ArrayCast;
use FriendsOfHyperf\ValidatedDTO\Casting\Castable;
use FriendsOfHyperf\ValidatedDTO\Casting\EnumCast;
use FriendsOfHyperf\ValidatedDTO\Concerns\DataResolver;
use FriendsOfHyperf\ValidatedDTO\Concerns\DataTransformer;
use FriendsOfHyperf\ValidatedDTO\Contract\BaseDTO;
use FriendsOfHyperf\ValidatedDTO\Exception\CastTargetException;
use FriendsOfHyperf\ValidatedDTO\Exception\MissingCastTypeException;
use Hyperf\Collection\Collection;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\CastsAttributes;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ValidatorInterface;
use Hyperf\Database\Model\Model;
use Hyperf\HttpMessage\Upload\UploadedFile;
use Hyperf\Validation\ValidationException;
use ReflectionClass;
use ReflectionProperty;
use UnitEnum;

abstract class SimpleDTO implements BaseDTO, CastsAttributes
{
    use DataResolver;
    use DataTransformer;

    protected array $data = [];

    protected array $validatedData = [];

    protected bool $requireCasting = false;

    protected ?ValidatorInterface $validator = null;

    protected array $dtoRules = [];

    protected array $dtoMessages = [];

    protected array $dtoDefaults = [];

    protected array $dtoCasts = [];

    protected array $dtoMapData = [];

    protected array $dtoMapTransform = [];

    /**
     * @throws ValidationException|MissingCastTypeException|CastTargetException
     */
    public function __construct(?array $data = null)
    {
        if (is_null($data)) {
            return;
        }

        $this->buildAttributesData();

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
     * Cast the given value to a DTO instance.
     *
     * @param Model $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     *
     * @throws ValidationException|MissingCastTypeException|CastTargetException
     */
    public function get($model, $key, $value, $attributes): static
    {
        $arrayCast = new ArrayCast();

        return new static($arrayCast->cast($key, $value));
    }

    /**
     * Prepare the value for storage.
     *
     * @param Model $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     * @return string
     */
    public function set($model, $key, $value, $attributes)
    {
        return match (true) {
            is_string($value) => $value,
            is_array($value) => json_encode($value),
            $value instanceof ValidatedDTO => $value->toJson(),
            default => '',
        };
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
    protected function mapData(): array
    {
        return [];
    }

    /**
     * Maps the DTO properties before the DTO export.
     */
    protected function mapToTransform(): array
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
        /** @var array<string, Castable> $casts */
        $casts = $this->buildCasts();

        foreach ($this->validatedData as $key => $value) {
            $this->{$key} = $value;
        }

        $defaults = [
            ...$this->defaults(),
            ...$this->dtoDefaults,
        ];

        foreach ($defaults as $key => $value) {
            if (
                ! property_exists($this, $key)
                || ! isset($this->{$key})
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

        $this->data = [];
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
        $casts = $this->buildCasts();

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

    protected function buildCasts(): array
    {
        $casts = [];
        foreach ($this->dtoCasts as $property => $cast) {
            if (is_null($cast->param)) {
                $casts[$property] = new $cast->type();

                continue;
            }

            $param = $cast->type === EnumCast::class
                ? $cast->param
                : new $cast->param();

            $casts[$property] = new $cast->type($param);
        }

        return [
            ...$this->casts(),
            ...$casts,
        ];
    }

    private function buildAttributesData(): void
    {
        $publicProperties = $this->getPublicProperties();

        $validatedProperties = $this->getPropertiesForAttribute($publicProperties, Rules::class);
        foreach ($validatedProperties as $property => $attribute) {
            $attributeInstance = $attribute->newInstance();
            $this->dtoRules[$property] = $attributeInstance->rules;
            $this->dtoMessages[$property] = $attributeInstance->messages ?? [];
        }

        $this->dtoMessages = array_filter(
            $this->dtoMessages,
            fn ($value) => $value !== []
        );

        $defaultProperties = $this->getPropertiesForAttribute($publicProperties, DefaultValue::class);
        foreach ($defaultProperties as $property => $attribute) {
            $attributeInstance = $attribute->newInstance();
            $this->dtoDefaults[$property] = $attributeInstance->value;
        }

        $castProperties = $this->getPropertiesForAttribute($publicProperties, Cast::class);
        foreach ($castProperties as $property => $attribute) {
            $attributeInstance = $attribute->newInstance();
            $this->dtoCasts[$property] = $attributeInstance;
        }

        $mapDataProperties = $this->getPropertiesForAttribute($publicProperties, Map::class);
        foreach ($mapDataProperties as $property => $attribute) {
            $attributeInstance = $attribute->newInstance();

            if (! blank($attributeInstance->data)) {
                $this->dtoMapData[$attributeInstance->data] = $property;
            }

            if (! blank($attributeInstance->transform)) {
                $this->dtoMapTransform[$property] = $attributeInstance->transform;
            }
        }
    }

    private function getPublicProperties(): array
    {
        $reflectionClass = new ReflectionClass($this);
        $dtoProperties = [];

        foreach ($reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            if ($this->isForbiddenProperty($property->getName())) {
                continue;
            }

            $reflectionProperty = new ReflectionProperty($this, $property->getName());
            $attributes = $reflectionProperty->getAttributes();
            $dtoProperties[$property->getName()] = $attributes;
        }

        return $dtoProperties;
    }

    private function getPropertiesForAttribute(array $properties, string $attribute): array
    {
        $result = [];
        foreach ($properties as $property => $attributes) {
            foreach ($attributes as $attr) {
                if ($attr->getName() === $attribute) {
                    $result[$property] = $attr;
                }
            }
        }

        return $result;
    }

    private function buildDataForValidation(array $data): array
    {
        $mapping = [
            ...$this->mapData(),
            ...$this->dtoMapData,
        ];

        return $this->mapDTOData($mapping, $data);
    }

    private function buildDataForExport(): array
    {
        $mapping = [
            ...$this->mapToTransform(),
            ...$this->dtoMapTransform,
        ];

        return $this->mapDTOData($mapping, $this->validatedData);
    }

    private function mapDTOData(array $mapping, array $data): array
    {
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

            if (isset($this->{$key}) && $value !== $this->{$key}) {
                $value = $this->{$key};
            }

            $mappedData[$property] = $this->isArrayable($value)
            ? $this->formatArrayableValue($value)
            : $value;
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
            || (is_object($value) && ! ($value instanceof UploadedFile));
    }

    private function formatArrayableValue(mixed $value): array|int|string
    {
        return match (true) {
            is_array($value) => $value,
            $value instanceof BackedEnum => $value->value,
            $value instanceof UnitEnum => $value->name,
            $value instanceof Carbon || $value instanceof CarbonImmutable => $value->toISOString(),
            $value instanceof Collection => $this->transformCollectionToArray($value),
            $value instanceof Model => $this->transformModelToArray($value),
            $value instanceof SimpleDTO => $this->transformDTOToArray($value),
            is_object($value) => (array) $value,
            default => [],
        };
    }

    private function transformCollectionToArray(Collection $collection): array
    {
        return $collection->map(function ($item) {
            return $this->isArrayable($item)
                ? $this->formatArrayableValue($item)
                : $item;
        })->toArray();
    }

    private function transformModelToArray(Model $model): array
    {
        $result = [];
        foreach ($model->getAttributes() as $key => $value) {
            $result[$key] = $this->isArrayable($value)
                ? $this->formatArrayableValue($value)
                : $value;
        }

        return $result;
    }

    private function transformDTOToArray(SimpleDTO $dto): array
    {
        $result = [];
        foreach ($dto->validatedData as $key => $value) {
            $result[$key] = $this->isArrayable($value)
                ? $this->formatArrayableValue($value)
                : $value;
        }

        return $result;
    }

    private function initConfig(): void
    {
        $config = null;

        if (ApplicationContext::hasContainer()) {
            $config = ApplicationContext::getContainer()->get(ConfigInterface::class)?->get('dto');
        }

        if (! empty($config) && array_key_exists('require_casting', $config)) {
            $this->requireCasting = $config['require_casting'];
        }
    }

    private function getAcceptedProperties(): array
    {
        $acceptedKeys = [];
        foreach (get_class_vars($this::class) as $key => $value) {
            if (! $this->isForbiddenProperty($key)) {
                $acceptedKeys[] = $key;
            }
        }

        return $acceptedKeys;
    }

    private function isForbiddenProperty(string $property): bool
    {
        return in_array($property, [
            'data',
            'validatedData',
            'requireCasting',
            'validator',
            'dtoRules',
            'dtoMessages',
            'dtoDefaults',
            'dtoCasts',
            'dtoMapData',
            'dtoMapTransform',
        ]);
    }
}
