<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace Pest\Hyperf;

use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use Pest\Contracts\Plugins\HandlesArguments;
use Pest\Exceptions\InvalidOption;
use Pest\Kernel;
use Pest\Plugins\Concerns\HandleArguments;
use Pest\Support\Container;
use Swoole\Coroutine;
use Swoole\Timer;

final class Plugin implements HandlesArguments
{
    use HandleArguments;

    public function handleArguments(array $arguments): array
    {
        $arguments = $this->prepend($arguments);

        if (! $this->hasArgument('--coroutine', $arguments)) {
            return $arguments;
        }

        $arguments = $this->popArgument('--coroutine', $arguments);

        if (Coroutine::getCid() > 0) {
            return $arguments;
        }

        if ($this->hasArgument('--parallel', $arguments) || $this->hasArgument('-p', $arguments)) {
            throw new InvalidOption('The [--coroutine] option is not supported when running in parallel.');
        }

        exit($this->runTestsInCoroutine($arguments));
    }

    private function runTestsInCoroutine(array $arguments): int
    {
        $code = 0;
        /** @var Kernel $kernel */
        $kernel = Container::getInstance()->get(Kernel::class);

        Coroutine::set([
            'hook_flags' => SWOOLE_HOOK_ALL,
            'exit_condition' => fn () => Coroutine::stats()['coroutine_num'] === 0,
        ]);

        /* @phpstan-ignore-next-line */
        \Swoole\Coroutine\run(static function () use (&$code, $kernel, $arguments) {
            $code = $kernel->handle($arguments);
            Timer::clearAll();
            CoordinatorManager::until(Constants::WORKER_EXIT)->resume();
        });

        $kernel->shutdown();

        return $code;
    }

    private function prepend(array $arguments): array
    {
        $prepend = null;

        foreach ($arguments as $key => $argument) {
            if (str_starts_with($argument, '--prepend=')) {
                $prepend = explode('=', $argument, 2)[1];
                unset($arguments[$key]);
                break;
            }

            if (str_starts_with($argument, '--prepend')) {
                if (isset($arguments[$key + 1])) {
                    $prepend = $arguments[$key + 1];
                    unset($arguments[$key + 1]);
                }
                unset($arguments[$key]);
            }
        }

        if ($prepend && file_exists($prepend)) {
            require_once $prepend;
        }

        return $arguments;
    }
}
