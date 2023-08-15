<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.0/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\PrettyConsole\View\Components;

class Confirm extends Component
{
    /**
     * Renders the component using the given arguments.
     *
     * @param string $question
     * @param bool $default
     * @return bool
     */
    public function render($question, $default = false)
    {
        return $this->usingQuestionHelper(
            fn () => $this->output->confirm($question, $default),
        );
    }
}
