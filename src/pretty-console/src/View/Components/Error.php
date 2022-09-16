<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\PrettyConsole\View\Components;

use Symfony\Component\Console\Output\OutputInterface;

class Error extends Component
{
    /**
     * Renders the component using the given arguments.
     *
     * @param string $string
     * @param int $verbosity
     */
    public function render($string, $verbosity = OutputInterface::VERBOSITY_NORMAL)
    {
        with(new Line($this->output))->render('error', $string, $verbosity);
    }
}
