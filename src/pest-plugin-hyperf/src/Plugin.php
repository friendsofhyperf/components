<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace Pest\Hyperf;

use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Di\ClassLoader;
use Hyperf\Support\Composer;
use Pest\Contracts\Plugins\HandlesArguments;
use Pest\Exceptions\InvalidOption;
use Pest\Kernel;
use Pest\Plugins\Concerns\HandleArguments;
use Pest\Support\Container;
use PHPUnit\TextUI\Application;
use Swoole\Coroutine;
use Swoole\Timer;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @property string $vendorDir
 */
class Plugin implements HandlesArguments
{
    use HandleArguments;

    public function handleArguments(array $arguments): array
    {
        if (Coroutine::getCid() > 0) {
            return $arguments;
        }

        if (! $this->hasArgument('--coroutine', $arguments)) {
            return $arguments;
        }

        $arguments = $this->popArgument('--coroutine', $arguments);

        if ($this->hasArgument('--parallel', $arguments) || $this->hasArgument('-p', $arguments)) {
            throw new InvalidOption('The coroutine mode is not supported when running in parallel.');
        }

        if ($this->hasArgument('--scan', $arguments)) {
            $vendorDir = (fn () => $this->vendorDir)->call(Composer::getLoader());
            defined('BASE_PATH') or define('BASE_PATH', dirname($vendorDir, 1));
            ClassLoader::init();

            $arguments = $this->popArgument('--scan', $arguments);
        }

        $code = 0;
        $output = Container::getInstance()->get(OutputInterface::class);
        $kernel = new Kernel(
            new Application(),
            $output,
        );

        Coroutine::set(['hook_flags' => SWOOLE_HOOK_ALL, 'exit_condition' => function () {
            return Coroutine::stats()['coroutine_num'] === 0;
        }]);

        /* @phpstan-ignore-next-line */
        \Swoole\Coroutine\run(function () use (&$code, $kernel, $arguments) {
            $code = $kernel->handle($arguments);
            Timer::clearAll();
            CoordinatorManager::until(Constants::WORKER_EXIT)->resume();
        });

        exit($code);
    }
}
