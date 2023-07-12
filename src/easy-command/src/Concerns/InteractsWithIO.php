<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\EasyCommand\Concerns;

trait InteractsWithIO
{
    /**
     * @var \Symfony\Component\Console\Input\Input|null
     */
    private $input;

    /**
     * @var \Symfony\Component\Console\Style\SymfonyStyle|null
     */
    private $output;
}
