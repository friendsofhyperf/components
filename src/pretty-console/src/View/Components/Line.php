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

class Line extends Component
{
    /**
     * The possible line styles.
     *
     * @var array<string, array<string, string>>
     */
    protected static $styles = [
        'info' => [
            'bgColor' => 'blue',
            'fgColor' => 'white',
            'title' => 'info',
        ],
        'warn' => [
            'bgColor' => 'yellow',
            'fgColor' => 'black',
            'title' => 'warn',
        ],
        'error' => [
            'bgColor' => 'red',
            'fgColor' => 'white',
            'title' => 'error',
        ],
    ];

    /**
     * Renders the component using the given arguments.
     *
     * @param string $style
     * @param string $string
     * @param int $verbosity
     */
    public function render($style, $string, $verbosity = OutputInterface::VERBOSITY_NORMAL)
    {
        $string = $this->mutate($string, [
            Mutators\EnsureDynamicContentIsHighlighted::class,
            Mutators\EnsurePunctuation::class,
            Mutators\EnsureRelativePaths::class,
        ]);

        $this->renderView('line', array_merge(static::$styles[$style], [
            'marginTop' => 1,
            'content' => $string,
        ]), $verbosity);
    }
}
