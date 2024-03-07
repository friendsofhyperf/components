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

use Symfony\Component\Console\Question\Question;

class AskWithCompletion extends Component
{
    /**
     * Renders the component using the given arguments.
     *
     * @param string $question
     * @param array|callable $choices
     * @param string $default
     * @return mixed
     */
    public function render($question, $choices, $default = null)
    {
        $question = new Question($question, $default);

        is_callable($choices)
            ? $question->setAutocompleterCallback($choices)
            : $question->setAutocompleterValues($choices);

        return $this->usingQuestionHelper(
            fn () => $this->output->askQuestion($question)
        );
    }
}
