<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.0/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\IdeHelper\Command;

use Barryvdh\Reflection\DocBlock;
use Barryvdh\Reflection\DocBlock\Context;
use Barryvdh\Reflection\DocBlock\Serializer as DocBlockSerializer;
use Barryvdh\Reflection\DocBlock\Tag;
use Composer\Autoload\ClassMapGenerator;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Database\Model\Relations\Relation;
use Hyperf\Stringable\Str;
use Hyperf\Support\Filesystem\Filesystem;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionObject;
use ReflectionType;
use ReflectionUnionType;
use SplFileObject;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class ModelCommand extends HyperfCommand
{
    protected ?string $signature = 'ide-helper:model {--I|ignore= : What prefix that you want the Model set.} {--N|name=_ide_helper_models.php : Name of IDE Helper.}';

    protected string $description = 'Generate a new Model IDE Helper file.';

    protected bool $writeModelMagicWhere = true;

    protected array $properties = [];

    protected array $methods = [];

    protected bool $write = false;

    protected bool $reset = true;

    protected bool $keepText = true;

    private array $ignore = [];

    private array $dirs = ['app'];

    private string $dateClass = '\Carbon\Carbon';

    private array $nullableColumns = [];

    public function __construct(protected ContainerInterface $container, protected Filesystem $filesystem, protected ConfigInterface $config)
    {
        parent::__construct();
    }

    /**
     * 执行CLI.
     */
    public function handle()
    {
        $this->loadIgnore();

        $content = $this->generateDocs([]);
        $filename = $this->input->getOption('name');
        $this->filesystem->put($filename, $content);
    }

    /**
     * Get the parameters and format them correctly.
     */
    protected function getParameters(ReflectionMethod $method): array
    {
        // Loop through the default values for parameters, and make the correct output string
        $params = [];
        $paramsWithDefault = [];

        foreach ($method->getParameters() as $param) {
            $paramClass = $param->getDeclaringClass();
            $paramStr = (! is_null($paramClass) ? '\\' . $paramClass->getName() . ' ' : '') . '$' . $param->getName();
            $params[] = $paramStr;
            if ($param->isOptional() && $param->isDefaultValueAvailable()) {
                $default = $param->getDefaultValue();
                if (is_bool($default)) {
                    $default = $default ? 'true' : 'false';
                } elseif (is_array($default)) {
                    $default = '[]';
                } elseif (is_null($default)) {
                    $default = 'null';
                } elseif (is_int($default)) {
                    // $default = $default;
                } else {
                    $default = "'" . trim($default) . "'";
                }
                $paramStr .= " = {$default}";
            }
            $paramsWithDefault[] = $paramStr;
        }

        return $paramsWithDefault;
    }

    protected function getOption($key, $default = null)
    {
        return $this->input->getOption($key) ?? $default;
    }

    protected function generateDocs($loadModels): string
    {
        $output = "<?php
// @formatter:off
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */
\n\n";

        $output .= \FriendsOfHyperf\IdeHelper\Eloquent::make();

        $hasDoctrine = interface_exists('Doctrine\DBAL\Driver');

        if (empty($loadModels)) {
            $models = $this->loadModels();
        } else {
            $models = [];
            foreach ($loadModels as $model) {
                $models = array_merge($models, explode(',', $model));
            }
        }

        foreach ($models as $name) {
            if (in_array($name, $this->ignore)) {
                if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                    $this->comment("Ignoring model '{$name}'");
                }
                continue;
            }
            $this->properties = [];
            $this->methods = [];
            if (class_exists($name)) {
                try {
                    // handle abstract classes, interfaces, ...
                    $reflectionClass = new ReflectionClass($name);

                    if (! $reflectionClass->isSubclassOf('Hyperf\DbConnection\Model\Model')) {
                        continue;
                    }

                    if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                        $this->comment("Loading model '{$name}'");
                    }

                    if (! $reflectionClass->IsInstantiable()) {
                        // ignore abstract class or interface
                        continue;
                    }
                    $model = $this->container->get($name);

                    if ($hasDoctrine) {
                        $this->getPropertiesFromTable($model);
                    }

                    if (method_exists($model, 'getCasts')) {
                        $this->castPropertiesType($model);
                    }

                    $this->getPropertiesFromMethods($model);
                    $this->getSoftDeleteMethods($model);
                    $output .= $this->createPhpDocs($name);
                    $ignore[] = $name;
                    $this->nullableColumns = [];
                } catch (Throwable $e) {
                    $this->error('Exception: ' . $e->getMessage() .
                                 "\nCould not analyze class {$name}.\n\nTrace:\n" .
                                 $e->getTraceAsString());
                }
            }
        }

        return $output;
    }

    protected function loadModels(): array
    {
        $models = [];

        foreach ($this->dirs as $dir) {
            $dir = BASE_PATH . '/' . $dir;
            if (file_exists($dir)) {
                foreach (ClassMapGenerator::createMap($dir) as $model => $path) {
                    $models[] = $model;
                }
            }
        }

        return $models;
    }

    /**
     * cast the properties 's type from $casts.
     *
     * @param \Hyperf\Database\Model\Model $model
     */
    protected function castPropertiesType($model)
    {
        $casts = $model->getCasts();
        foreach ($casts as $name => $type) {
            switch ($type) {
                case 'boolean':
                case 'bool':
                    $realType = 'boolean';
                    break;
                case 'string':
                    $realType = 'string';
                    break;
                case 'array':
                case 'json':
                    $realType = 'array';
                    break;
                case 'object':
                    $realType = 'object';
                    break;
                case 'int':
                case 'integer':
                case 'timestamp':
                    $realType = 'integer';
                    break;
                case 'real':
                case 'double':
                case 'float':
                    $realType = 'float';
                    break;
                case 'date':
                case 'datetime':
                    $realType = $this->dateClass;
                    break;
                case 'collection':
                    $realType = '\Hyperf\Collection\Collection';
                    break;
                default:
                    $realType = class_exists($type) ? ('\\' . $type) : 'mixed';
                    break;
            }
            if (! isset($this->properties[$name])) {
                continue;
            }
            $this->properties[$name]['type'] = $this->getTypeOverride($realType);

            if (isset($this->nullableColumns[$name])) {
                $this->properties[$name]['type'] .= '|null';
            }
        }
    }

    /**
     * Returns the override type for the give type.
     */
    protected function getTypeOverride(string $type): string
    {
        $typeOverrides = $this->config->get('ide-helper.model.type_overrides', []);

        return $typeOverrides[$type] ?? $type;
    }

    /**
     * Load the properties from the database table.
     *
     * @param \Hyperf\Database\Model\Model $model
     */
    protected function getPropertiesFromTable($model)
    {
        /** @var \Hyperf\Database\Connection $connection */
        $connection = $model->getConnection();
        $table = $connection->getTablePrefix() . $model->getTable();
        $schema = $connection->getDoctrineSchemaManager($table);
        $databasePlatform = $schema->getDatabasePlatform();
        $databasePlatform->registerDoctrineTypeMapping('enum', 'string');

        $platformName = $databasePlatform->getName();
        $customTypes = $this->config->get("ide-helper.model.custom_db_types.{$platformName}", []);

        foreach ($customTypes as $yourTypeName => $doctrineTypeName) {
            $databasePlatform->registerDoctrineTypeMapping($yourTypeName, $doctrineTypeName);
        }

        $database = null;

        if (strpos($table, '.')) {
            [$database, $table] = explode('.', $table);
        }

        $columns = $schema->listTableColumns($table, $database);

        if ($columns) {
            foreach ($columns as $column) {
                $name = $column->getName();
                if (in_array($name, $model->getDates())) {
                    $type = $this->dateClass;
                } else {
                    $type = $column->getType()->getName();
                    switch ($type) {
                        case 'string':
                        case 'text':
                        case 'date':
                        case 'time':
                        case 'guid':
                        case 'datetimetz':
                        case 'datetime':
                            $type = 'string';
                            break;
                        case 'integer':
                        case 'bigint':
                        case 'smallint':
                            $type = 'integer';
                            break;
                        case 'boolean':
                            $type = 'integer';
                            break;
                        case 'decimal':
                        case 'float':
                            $type = 'float';
                            break;
                        default:
                            $type = 'mixed';
                            break;
                    }
                }

                $comment = $column->getComment();
                if (! $column->getNotnull()) {
                    $this->nullableColumns[$name] = true;
                }
                $this->setProperty($name, $type, true, true, $comment, ! $column->getNotnull());
                if ($this->writeModelMagicWhere) {
                    $this->setMethod(
                        Str::camel('where_' . $name),
                        '\Hyperf\Database\Model\Builder|\\' . get_class($model),
                        ['$value']
                    );
                }
            }
        }
    }

    /**
     * @param \Hyperf\Database\Model\Model $model
     */
    protected function getPropertiesFromMethods($model)
    {
        $methods = get_class_methods($model);

        if ($methods) {
            sort($methods);
            foreach ($methods as $method) {
                if (str_starts_with($method, 'get') && str_ends_with(
                    $method,
                    'Attribute'
                ) && $method !== 'getAttribute'
                ) {
                    // Magic get<name>Attribute
                    $name = Str::snake(substr($method, 3, -9));
                    if (! empty($name)) {
                        $reflection = new ReflectionMethod($model, $method);
                        $type = $this->getReturnType($reflection);
                        $this->setProperty($name, $type, true, null);
                    }
                } elseif (str_starts_with($method, 'set') && str_ends_with(
                    $method,
                    'Attribute'
                ) && $method !== 'setAttribute'
                ) {
                    // Magic set<name>Attribute
                    $name = Str::snake(substr($method, 3, -9));
                    if (! empty($name)) {
                        $this->setProperty($name, null, null, true);
                    }
                } elseif (str_starts_with($method, 'scope') && $method !== 'scopeQuery') {
                    // Magic set<name>Attribute
                    $name = Str::camel(substr($method, 5));
                    if (! empty($name)) {
                        $reflection = new ReflectionMethod($model, $method);
                        $args = $this->getParameters($reflection);
                        // Remove the first ($query) argument
                        array_shift($args);
                        $this->setMethod($name, '\Hyperf\Database\Model\Builder|\\' . $reflection->class, $args);
                    }
                } elseif (in_array($method, ['query', 'newQuery', 'newModelQuery'])) {
                    $reflection = new ReflectionClass($model);

                    $builder = get_class($model->newModelQuery());

                    $this->setMethod($method, "\\{$builder}|\\" . $reflection->getName());
                } elseif (! method_exists('Hyperf\DbConnection\Model\Model', $method) && ! str_starts_with($method, 'get')) {
                    $reflection = new ReflectionMethod($model, $method);

                    if ($returnType = $reflection->getReturnType()) {
                        $type = $returnType instanceof ReflectionNamedType
                            ? $returnType->getName()
                            : (string) $returnType;
                    } else {
                        // php 7.x type or fallback to docblock
                        $type = (string) $this->getReturnTypeFromDocBlock($reflection);
                    }

                    $file = new SplFileObject($reflection->getFileName());
                    $file->seek($reflection->getStartLine() - 1);

                    $code = '';
                    while ($file->key() < $reflection->getEndLine()) {
                        $code .= $file->current();
                        $file->next();
                    }
                    $code = trim(preg_replace('/\s\s+/', '', $code));
                    $begin = strpos($code, 'function');

                    $code = substr($code, $begin, strrpos($code, '}') - $begin + 1);

                    foreach ([
                        'hasMany' => '\Hyperf\Database\Model\Relations\HasMany',
                        'hasManyThrough' => '\Hyperf\Database\Model\Relations\HasManyThrough',
                        'hasOneThrough' => '\Hyperf\Database\Model\Relations\HasOneThrough',
                        'belongsToMany' => '\Hyperf\Database\Model\Relations\BelongsToMany',
                        'hasOne' => '\Hyperf\Database\Model\Relations\HasOne',
                        'belongsTo' => '\Hyperf\Database\Model\Relations\BelongsTo',
                        'morphOne' => '\Hyperf\Database\Model\Relations\MorphOne',
                        'morphTo' => '\Hyperf\Database\Model\Relations\MorphTo',
                        'morphMany' => '\Hyperf\Database\Model\Relations\MorphMany',
                        'morphToMany' => '\Hyperf\Database\Model\Relations\MorphToMany',
                        'morphedByMany' => '\Hyperf\Database\Model\Relations\MorphToMany',
                    ] as $relation => $impl) {
                        $search = '$this->' . $relation . '(';
                        if (stripos($code, $search) || ltrim($impl, '\\') === ltrim((string) $type, '\\')) {
                            // Resolve the relation's model to a Relation object.
                            $methodReflection = new ReflectionMethod($model, $method);
                            if ($methodReflection->getNumberOfParameters()) {
                                continue;
                            }

                            // Adding constraints requires reading model properties which
                            // can cause errors. Since we don't need constraints we can
                            // disable them when we fetch the relation to avoid errors.
                            $relationObj = Relation::noConstraints(function () use ($model, $method) {
                                return $model->{$method}();
                            });

                            if ($relationObj instanceof Relation) {
                                $relatedModel = '\\' . get_class($relationObj->getRelated());

                                $relations = [
                                    'hasManyThrough',
                                    'belongsToMany',
                                    'hasMany',
                                    'morphMany',
                                    'morphToMany',
                                    'morphedByMany',
                                ];
                                if (strpos(get_class($relationObj), 'Many') !== false) {
                                    // Collection or array of models (because Collection is Arrayable)
                                    $this->setProperty(
                                        $method,
                                        $this->getCollectionClass($relatedModel) . '|' . $relatedModel . '[]',
                                        true,
                                        null
                                    );
                                    $this->setProperty(
                                        Str::snake($method) . '_count',
                                        'int|null',
                                        true,
                                        false
                                    );
                                } elseif ($relation === 'morphTo') {
                                    // Model isn't specified because relation is polymorphic
                                    $this->setProperty(
                                        $method,
                                        '\Hyperf\DbConnection\Model\Model|\Eloquent',
                                        true,
                                        null
                                    );
                                } else {
                                    // Single model is returned
                                    $this->setProperty(
                                        $method,
                                        $relatedModel,
                                        true,
                                        null,
                                        '',
                                        $this->isRelationForeignKeyNullable($relationObj)
                                    );
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    protected function setProperty(string $name, ?string $type = null, ?bool $read = null, ?bool $write = null, ?string $comment = '', bool $nullable = false)
    {
        if (! isset($this->properties[$name])) {
            $this->properties[$name] = [];
            $this->properties[$name]['type'] = 'mixed';
            $this->properties[$name]['read'] = false;
            $this->properties[$name]['write'] = false;
            $this->properties[$name]['comment'] = (string) $comment;
        }
        if ($type !== null) {
            $newType = $this->getTypeOverride($type);
            if ($nullable) {
                $newType .= '|null';
            }
            $this->properties[$name]['type'] = $newType;
        }
        if ($read !== null) {
            $this->properties[$name]['read'] = $read;
        }
        if ($write !== null) {
            $this->properties[$name]['write'] = $write;
        }
    }

    protected function setMethod(string $name, string $type = '', array $arguments = [])
    {
        $methods = array_change_key_case($this->methods, CASE_LOWER);

        if (! isset($methods[strtolower($name)])) {
            $this->methods[$name] = [];
            $this->methods[$name]['type'] = $type;
            $this->methods[$name]['arguments'] = $arguments;
        }
    }

    /**
     * @return string
     */
    protected function createPhpDocs(string $class)
    {
        $reflection = new ReflectionClass($class);
        $namespace = $reflection->getNamespaceName();
        $classname = $reflection->getShortName();
        $originalDoc = $reflection->getDocComment();
        $keyword = $this->getClassKeyword($reflection);

        if ($this->reset) {
            $phpdoc = new DocBlock('', new Context($namespace));
            if ($this->keepText) {
                $phpdoc->setText(
                    (new DocBlock($reflection, new Context($namespace)))->getText()
                );
            }
        } else {
            $phpdoc = new DocBlock($reflection, new Context($namespace));
        }

        if (! $phpdoc->getText()) {
            $phpdoc->setText($class);
        }

        $properties = [];
        $methods = [];
        foreach ($phpdoc->getTags() as $tag) {
            /** @var \Barryvdh\Reflection\DocBlock\Tag\MethodTag|\Barryvdh\Reflection\DocBlock\Tag\ParamTag $tag */
            $name = $tag->getName();
            if ($name == 'property' || $name == 'property-read' || $name == 'property-write') {
                $properties[] = $tag->getVariableName();
            } elseif ($name == 'method') {
                $methods[] = $tag->getMethodName();
            }
        }

        foreach ($this->properties as $name => $property) {
            $name = "\${$name}";

            if ($this->hasCamelCaseModelProperties()) {
                $name = Str::camel($name);
            }

            if (in_array($name, $properties)) {
                continue;
            }
            if ($property['read'] && $property['write']) {
                $attr = 'property';
            } elseif ($property['write']) {
                $attr = 'property-write';
            } else {
                $attr = 'property-read';
            }

            $tagLine = trim("@{$attr} {$property['type']} {$name} {$property['comment']}");
            $tag = Tag::createInstance($tagLine, $phpdoc);
            $phpdoc->appendTag($tag);
        }

        ksort($this->methods);

        foreach ($this->methods as $name => $method) {
            if (in_array($name, $methods)) {
                continue;
            }
            $arguments = implode(', ', $method['arguments']);
            $tag = Tag::createInstance("@method static {$method['type']} {$name}({$arguments})", $phpdoc);
            $phpdoc->appendTag($tag);
        }

        if ($this->write && ! $phpdoc->getTagsByName('mixin')) {
            $phpdoc->appendTag(Tag::createInstance('@mixin \\Eloquent', $phpdoc));
        }

        $serializer = new DocBlockSerializer();
        $serializer->getDocComment($phpdoc);
        $docComment = $serializer->getDocComment($phpdoc);

        if ($this->write) {
            $filename = $reflection->getFileName();
            $contents = $this->filesystem->get($filename);
            if ($originalDoc) {
                $contents = str_replace($originalDoc, $docComment, $contents);
            } else {
                $needle = "class {$classname}";
                $replace = "{$docComment}\nclass {$classname}";
                $pos = strpos($contents, $needle);
                if ($pos !== false) {
                    $contents = substr_replace($contents, $replace, $pos, strlen($needle));
                }
            }
            if ($this->filesystem->put($filename, $contents)) {
                $this->info('Written new phpDocBlock to ' . $filename);
            }
        }

        return "namespace {$namespace}{\n{$docComment}\n\t{$keyword}class {$classname} extends \\Eloquent {}\n}\n\n";
    }

    protected function hasCamelCaseModelProperties(): bool
    {
        return (bool) $this->config->get('ide-helper.model.camel_case_properties', false);
    }

    protected function getReturnType(ReflectionMethod $reflection): ?string
    {
        return $this->getReturnTypeFromDocBlock($reflection) ?: $this->getReturnTypeFromReflection($reflection);
    }

    /**
     * Get method return type based on it DocBlock comment.
     */
    protected function getReturnTypeFromDocBlock(ReflectionMethod $reflection): ?string
    {
        $type = null;
        $phpdoc = new DocBlock($reflection);

        if ($phpdoc->hasTag('return')) {
            /** @var \Barryvdh\Reflection\DocBlock\Tag\ReturnTag $tag */
            $tag = $phpdoc->getTagsByName('return')[0];
            $type = $tag->getType();
        }

        return $type;
    }

    protected function getReturnTypeFromReflection(ReflectionMethod $reflection): ?string
    {
        $returnType = $reflection->getReturnType();

        if (! $returnType) {
            return null;
        }

        $types = $this->extractReflectionTypes($returnType);

        if ($returnType->allowsNull()) {
            $types[] = 'null';
        }

        return implode('|', $types);
    }

    /**
     * @return string|string[]
     */
    protected function extractReflectionTypes(ReflectionType $reflectionType)
    {
        if ($reflectionType instanceof ReflectionNamedType) {
            return $this->getReflectionNamedType($reflectionType);
        }

        /** @var ReflectionUnionType $reflectionType */
        $types = [];

        foreach ($reflectionType->getTypes() as $namedType) {
            if ($namedType->getName() === 'null') {
                continue;
            }

            $types[] = $this->getReflectionNamedType($namedType);
        }

        return $types;
    }

    protected function getReflectionNamedType(ReflectionNamedType $paramType): string
    {
        $parameterName = $paramType->getName();

        if (! $paramType->isBuiltin()) {
            $parameterName = '\\' . $parameterName;
        }

        return $parameterName;
    }

    /**
     * Generates methods provided by the SoftDeletes trait.
     *
     * @param \Hyperf\Database\Model\Model $model
     */
    protected function getSoftDeleteMethods($model)
    {
        $traits = class_uses(get_class($model), true);
        if (in_array('Hyperf\\Database\\Model\\SoftDeletes', $traits)) {
            $this->setMethod('forceDelete', 'bool|null', []);
            $this->setMethod('restore', 'bool|null', []);

            $this->setMethod('withTrashed', '\Hyperf\Database\Model\Builder|\\' . get_class($model), []);
            $this->setMethod('withoutTrashed', '\Hyperf\Database\Model\Builder|\\' . get_class($model), []);
            $this->setMethod('onlyTrashed', '\Hyperf\Database\Model\Builder|\\' . get_class($model), []);
        }
    }

    private function loadIgnore()
    {
        $ignore = $this->getOption('ignore', '');
        $this->ignore = array_merge(
            explode(',', $ignore),
            $this->config->get('ide-helper.model.ignores', [])
        );
    }

    /**
     * Check if the foreign key of the relation is nullable.
     */
    private function isRelationForeignKeyNullable(Relation $relation): bool
    {
        $reflectionObj = new ReflectionObject($relation);
        if (! $reflectionObj->hasProperty('foreignKey')) {
            return false;
        }
        $fkProp = $reflectionObj->getProperty('foreignKey');
        $fkProp->setAccessible(true);

        return isset($this->nullableColumns[$fkProp->getValue($relation)]);
    }

    /**
     * Determine a model classes' collection type.
     *
     * @see http://laravel.com/docs/eloquent-collections#custom-collections
     * @param string $className
     */
    private function getCollectionClass($className): string
    {
        // Return something in the very very unlikely scenario the model doesn't
        // have a newCollection() method.
        if (! method_exists($className, 'newCollection')) {
            return '\Hyperf\Database\Model\Collection';
        }

        /** @var \Hyperf\Database\Model\Model $model */
        $model = new $className();
        return '\\' . get_class($model->newCollection());
    }

    private function getClassKeyword(ReflectionClass $reflection): string
    {
        if ($reflection->isFinal()) {
            $keyword = 'final ';
        } elseif ($reflection->isAbstract()) {
            $keyword = 'abstract ';
        } else {
            $keyword = '';
        }

        return $keyword;
    }
}
