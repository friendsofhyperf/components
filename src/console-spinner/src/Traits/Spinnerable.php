<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\ConsoleSpinner\Traits;

use Closure;
use Countable;
use FriendsOfHyperf\ConsoleSpinner\Spinner;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Utils\ApplicationContext;
use TypeError;

trait Spinnerable
{
    /**
     * @throws TypeError
     * @return Spinner
     */
    protected function spinner(int $max = 0)
    {
        /** @var ConfigInterface $config */
        $config = ApplicationContext::getContainer()->get(ConfigInterface::class);

        return make(Spinner::class, [
            'output' => $this->output,
            'max' => $max,
            'chars' => $config->get('console_spinner.chars'),
        ]);
    }

    /**
     * @param array|Countable|int $totalSteps
     * @throws TypeError
     * @return mixed
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
