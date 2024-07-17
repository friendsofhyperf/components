<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Tinker;

use ReflectionClass;
use ReflectionProperty;
use Stringable;
use Symfony\Component\VarDumper\Caster\Caster;
use Throwable;

/**
 * @property string $poolName
 * @property array $appends
 */
class TinkerCaster
{
    /**
     * Application methods to include in the presenter.
     */
    private static array $appProperties = [
        'configurationIsCached',
        'environment',
        'environmentFile',
        'isLocal',
        'routesAreCached',
        'runningUnitTests',
        'version',
        'path',
        'basePath',
        'configPath',
        'databasePath',
        'langPath',
        'publicPath',
        'storagePath',
        'bootstrapPath',
    ];

    /**
     * Get an array representing the properties of an application.
     *
     * @param \Symfony\Component\Console\Application $app
     */
    public static function castApplication($app): array
    {
        $results = [];

        foreach (self::$appProperties as $property) {
            try {
                if (! is_callable([$app, $property])) {
                    continue;
                }

                $val = $app->{$property}();

                if (! is_null($val)) {
                    $results[Caster::PREFIX_VIRTUAL . $property] = $val;
                }
            } catch (Throwable) {
            }
        }

        return $results;
    }

    /**
     * Get an array representing the properties of a collection.
     *
     * @param \Hyperf\Collection\Collection $collection
     */
    public static function castCollection($collection): array
    {
        return [
            Caster::PREFIX_VIRTUAL . 'all' => $collection->all(),
        ];
    }

    /**
     * Get an array representing the properties of an html string.
     *
     * @param \Hyperf\ViewEngine\HtmlString $htmlString
     */
    public static function castHtmlString($htmlString): array
    {
        return [
            Caster::PREFIX_VIRTUAL . 'html' => $htmlString->toHtml(),
        ];
    }

    /**
     * Get an array representing the properties of a fluent string.
     * @param Stringable $stringable
     */
    public static function castStringable($stringable): array
    {
        return [
            Caster::PREFIX_VIRTUAL . 'value' => (string) $stringable,
        ];
    }

    /**
     * Get an array representing the properties of a model.
     *
     * @param \Hyperf\DbConnection\Model\Model $model
     */
    public static function castModel($model): array
    {
        $attributes = array_merge(
            $model->getAttributes(),
            $model->getRelations()
        );

        $visible = array_flip(
            $model->getVisible() ?: array_diff(array_keys($attributes), $model->getHidden())
        );

        $hidden = array_flip($model->getHidden());
        $appends = (fn () => array_combine($this->appends, $this->appends))->bindTo($model, $model)();

        foreach ($appends as $appended) {
            $attributes[$appended] = $model->{$appended};
        }

        $results = [];

        foreach ($attributes as $key => $value) {
            $prefix = '';

            if (isset($visible[$key])) {
                $prefix = Caster::PREFIX_VIRTUAL;
            }

            if (isset($hidden[$key])) {
                $prefix = Caster::PREFIX_PROTECTED;
            }

            $results[$prefix . $key] = $value;
        }

        return $results;
    }

    /**
     * Get an array representing the properties of a redis.
     *
     * @param \Hyperf\Redis\Redis $redis
     * @return string[]
     */
    public static function castRedis($redis): array
    {
        $poolName = (fn () => $this->poolName ?? 'default')->call($redis);

        return [
            Caster::PREFIX_PROTECTED . 'poolName' => $poolName,
        ];
    }

    /**
     * Get an array representing the properties of a fluent.
     *
     * @param \Hyperf\Support\Fluent $fluent
     */
    public static function castFluent($fluent): array
    {
        return [
            Caster::PREFIX_PROTECTED . 'attributes' => $fluent->getAttributes(),
        ];
    }

    /**
     * Get an array representing the properties of a message bag.
     *
     * @param \Hyperf\Support\MessageBag $messageBag
     */
    public static function castMessageBag($messageBag): array
    {
        return [
            Caster::PREFIX_PROTECTED . 'messages' => $messageBag->getMessages(),
            Caster::PREFIX_PROTECTED . 'format' => $messageBag->getFormat(),
        ];
    }

    /**
     * @param \FriendsOfHyperf\ValidatedDTO\SimpleDTO $dto
     */
    public static function castSimpleDTO($dto): array
    {
        $reflectionClass = new ReflectionClass($dto);
        $results = [];

        foreach ($reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            $property->setAccessible(true);
            $results[Caster::PREFIX_VIRTUAL . $property->getName()] = $property->getValue($dto);
        }

        $results[Caster::PREFIX_PROTECTED . 'validatedData'] = (fn () => $this->validatedData ?? [])->call($dto);
        $results[Caster::PREFIX_VIRTUAL . $dto::class . '::toArray()'] = $dto->toArray();

        return $results;
    }
}
