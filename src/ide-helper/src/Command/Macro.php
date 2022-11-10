<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/2.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\IdeHelper\Command;

use Barryvdh\Reflection\DocBlock;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Macroable\Macroable;
use Hyperf\Utils\Filesystem\Filesystem;
use Hyperf\Utils\Str;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionFunction;
use ReflectionParameter;
use Swoole\Coroutine\System;
use Throwable;

/**
 * @Command
 */
#[Command]
class Macro extends HyperfCommand
{
    /**
     * @var string
     */
    protected $signature = 'ide-helper:macro {--N|name=_ide_helper_macros.php : Name of IDE Helper.}';

    /**
     * @var string
     */
    protected $description = 'Generate a new Macros IDE Helper file.';

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var ConfigInterface
     */
    private $config;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct();
        $this->filesystem = $container->get(Filesystem::class);
        $this->config = $container->get(ConfigInterface::class);
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

    protected function configure()
    {
        parent::configure();
        $this->setDescription($this->description);
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
                $defaultValue = $parameter->isArray() ? '[]' : ($parameter->getDefaultValue() ?? 'null');
                $defaultValue = preg_replace('/\s+/', ' ', var_export($defaultValue, 1));
                $parameterString .= sprintf(' = %s', $defaultValue);
            }
        }

        return $parameterString;
    }

    private function dumpAutoload()
    {
        $ret = System::exec('composer dump-autoload -o --no-scripts');
        $this->output->writeln($ret['output'] ?? '');
    }
}
