<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\PrettyConsole\View\Components;

use Symfony\Component\Console\Output\OutputInterface;

class BulletList extends Component
{
    /**
     * Renders the component using the given arguments.
     *
     * @param array<int, string> $elements
     * @param int $verbosity
     */
    public function render($elements, $verbosity = OutputInterface::VERBOSITY_NORMAL)
    {
        $elements = $this->mutate($elements, [
            Mutators\EnsureDynamicContentIsHighlighted::class,
            Mutators\EnsureNoPunctuation::class,
            Mutators\EnsureRelativePaths::class,
        ]);

        $this->renderView('bullet-list', [
            'elements' => $elements,
        ], $verbosity);
    }
}
