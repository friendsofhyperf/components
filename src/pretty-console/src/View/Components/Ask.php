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

class Ask extends Component
{
    /**
     * Renders the component using the given arguments.
     *
     * @param string $question
     * @param string $default
     * @return mixed
     */
    public function render($question, $default = null)
    {
        return $this->usingQuestionHelper(fn () => $this->output->ask($question, $default));
    }
}
