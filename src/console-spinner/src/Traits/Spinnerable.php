<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.0/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\ConsoleSpinner\Traits;

use Closure;
use Countable;
use FriendsOfHyperf\ConsoleSpinner\Spinner;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use TypeError;

use function Hyperf\Support\make;

trait Spinnerable
{
    /**
     * @throws TypeError
     */
    protected function spinner(int $max = 0): Spinner
    {
        /** @var ConfigInterface $config */
        $config = ApplicationContext::getContainer()->get(ConfigInterface::class);
        $chars = null;

        if ($config->has($key = 'console_spinner.chars')) {
            $chars = (array) $config->get($key);
        }

        return make(Spinner::class, [
            'output' => $this->output,
            'max' => $max,
            'chars' => $chars,
        ]);
    }

    /**
     * @param array|Countable|int $totalSteps
     * @return mixed
     * @throws TypeError
     */
    protected function withSpinner($totalSteps, Closure $callback, string $message = '')
    {
        $spinner = $this->spinner(
            is_iterable($totalSteps) ? count($totalSteps) : $totalSteps
        );
        $spinner->setMessage($message);
        $spinner->start();

        if (is_iterable($totalSteps)) {
            foreach ($totalSteps as $item) {
                $callback($item, $spinner);

                $spinner->advance();
            }
        } else {
            $callback($spinner);
        }

        $spinner->finish();

        if (is_iterable($spinner)) {
            return $totalSteps;
        }
    }
}
