<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.0/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\PrettyConsole\Traits;

use FriendsOfHyperf\PrettyConsole\View\Components\Factory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

trait Prettyable
{
    protected ?Factory $components = null;

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->components = new Factory($output);
    }
}
