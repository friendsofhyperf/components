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

use Symfony\Component\Console\Question\ChoiceQuestion;

class Choice extends Component
{
    /**
     * Renders the component using the given arguments.
     *
     * @param string $question
     * @param array<array-key, string> $choices
     * @param mixed $default
     * @return mixed
     */
    public function render($question, $choices, $default = null)
    {
        return $this->usingQuestionHelper(
            fn () => $this->output->askQuestion(
                new ChoiceQuestion($question, $choices, $default)
            ),
        );
    }
}
