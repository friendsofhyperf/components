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
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Macroable\Macroable;
use Hyperf\Stringable\Str;
use Hyperf\Support\Filesystem\Filesystem;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionFunction;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;
use Swoole\Coroutine\System;
use Throwable;

use function Hyperf\Collection\collect;
use function Hyperf\Support\optional;

class MacroCommand extends HyperfCommand
{
    protected ?string $signature = 'ide-helper:macro {--N|name=_ide_helper_macros.php : Name of IDE Helper.}';

    protected string $description = 'Generate a new Macros IDE Helper file.';

    public function __construct(protected ContainerInterface $container, protected Filesystem $filesystem, protected ConfigInterface $config)
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->dumpAutoload();

        $classMapFile = BASE_PATH . '/vendor/composer/autoload_classmap.php';

        if (! file_exists($classMapFile)) {
            $this->error("{$classMapFile} is not found");
            return;
        }

        $classMaps = include $classMapFile;
        $namespaces = $this->config->get('ide-helper.macro.namespaces', []);

        $docs = collect($classMaps)
            ->when(count($namespaces) > 0, function ($collection) use ($namespaces) {
                return $collection->filter(function ($path, $class) use ($namespaces) {
                    return Str::startsWith($class, $namespaces);
                });
            })
            ->reject(function ($path, $class) {
                $rejects = $this->config->get('ide-helper.macro.rejects', []);
                return in_array($class, $rejects);
            })
            ->mapWithKeys(function ($path, $class) {
                try {
                    $reflection = new ReflectionClass($class);
                    $traits = array_keys($reflection->getTraits() ?? []);

                    if (empty($traits) || ! in_array(Macroable::class, $traits)) {
                        return [];
                    }

                    return [$class => $reflection];
                } catch (Throwable $e) {
                    $this->warn($e->getMessage(), 'v');
                    return [];
                }
            })
            ->filter()
            ->mapToGroups(function ($reflection, $class) {
                try {
                    $namespace = $reflection->getNamespaceName();
                    $shortName = $reflection->getShortName();

                    $property = $reflection->getProperty('macros');
                    $property->setAccessible(true);
                    $macros = $property->getValue(null);

                    if (empty($macros)) {
                        return [];
                    }

                    $phpDoc = new DocBlock($reflection, new DocBlock\Context($namespace));
                    $phpDoc->setText($class);

                    foreach ($macros as $name => $closure) {
                        $macro = new ReflectionFunction($closure);

                        $params = join(', ', array_map([$this, 'prepareParameter'], $macro->getParameters()));
                        $doc = $macro->getDocComment();
                        $returnType = $doc && preg_match('/@return ([a-zA-Z\\[\\]\\|\\\\]+)/', $doc, $matches) ? $matches[1] : '';
                        $phpDoc->appendTag(DocBlock\Tag::createInstance("@method {$returnType} {$name}({$params})"));

                        $see = $macro->getClosureScopeClass()->getName();
                        $phpDoc->appendTag(DocBlock\Tag::createInstance("@see \\{{$see}}", $phpDoc));

                        $sourceFile = Str::replaceFirst(BASE_PATH . '/', '', $macro->getFileName());
                        $startLine = $macro->getStartLine();
                        $endLine = $macro->getEndLine();
                        $phpDoc->appendTag(DocBlock\Tag::createInstance("@see {$sourceFile} {$startLine} {$endLine}", $phpDoc));
                    }

                    $phpDoc->appendTag(DocBlock\Tag::createInstance('@package macro_ide_helper'));

                    $serializer = new DocBlock\Serializer();
                    $docComment = $serializer->getDocComment($phpDoc);

                    return [
                        $namespace => [
                            'shortName' => $shortName,
                            'docComment' => $docComment,
                        ],
                    ];
                } catch (Throwable $e) {
                    $this->warn($e->getMessage(), 'v');
                    return [];
                }
            })
            ->reject(function ($a, $class) {
                return ! $class;
            });

        $contents = [];
        $contents[] = '<?php';
        $contents[] = '// @formatter:off';

        foreach ($docs as $namespace => $classes) {
            $contents[] = "namespace {$namespace} {";
            $contents[] = '';

            foreach ($classes as $class) {
                $contents[] = $class['docComment'];
                $contents[] = "    class {$class['shortName']} {}";
                $contents[] = '';
            }
            $contents[] = '}';
            $contents[] = '';
        }

        $contents[] = 'namespace {}';
        $contents[] = '';

        $filename = $this->input->getOption('name');
        $this->filesystem->put($filename, join("\n", $contents));

        $this->info("A new helper file was written to {$filename}");
    }

    /**
     * parse parameters.
     */
    private function prepareParameter(ReflectionParameter $parameter): string
    {
        $parameterString = trim(optional($parameter->getType())->getName() . ' $' . $parameter->getName());

        if ($parameter->isOptional()) {
            if ($parameter->isVariadic()) {
                $parameterString = '...' . $parameterString;
            } else {
                $defaultValue = $this->isArray($parameter) ? '[]' : ($parameter->getDefaultValue() ?? 'null');
                $defaultValue = preg_replace('/\s+/', ' ', var_export($defaultValue, true));
                $parameterString .= sprintf(' = %s', $defaultValue);
            }
        }

        return $parameterString;
    }

    /**
     * Checks if the parameter expects an array.
     */
    private function isArray(ReflectionParameter $reflectionParameter): bool
    {
        $reflectionType = $reflectionParameter->getType();

        if (! $reflectionType) {
            return false;
        }

        $types = $reflectionType instanceof ReflectionUnionType
            ? $reflectionType->getTypes()
            : [$reflectionType];

        return in_array('array', array_map(fn (ReflectionNamedType $t) => $t->getName(), $types));
    }

    private function dumpAutoload()
    {
        $ret = System::exec('composer dump-autoload -o --no-scripts');
        $this->output->writeln($ret['output'] ?? '');
    }
}
