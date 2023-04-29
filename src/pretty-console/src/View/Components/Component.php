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

use FriendsOfHyperf\PrettyConsole\QuestionHelper;
use Hyperf\Contract\Arrayable;
use ReflectionClass;
use Symfony\Component\Console\Helper\SymfonyQuestionHelper;
use Symfony\Component\Console\Style\SymfonyStyle;

use function FriendsOfHyperf\Helpers\app;
use function Hyperf\Support\with;
use function Hyperf\Tappable\tap;
use function Termwind\render;
use function Termwind\renderUsing;

abstract class Component
{
    /**
     * The output style implementation.
     *
     * @var SymfonyStyle
     */
    protected $output;

    /**
     * The list of mutators to apply on the view data.
     *
     * @var array<int, callable(string): string>
     */
    protected $mutators;

    /**
     * Creates a new component instance.
     *
     * @param SymfonyStyle $output
     */
    public function __construct($output)
    {
        $this->output = $output;
    }

    /**
     * Renders the given view.
     *
     * @param string $view
     * @param array|Arrayable $data
     * @param int $verbosity
     */
    protected function renderView($view, $data, $verbosity)
    {
        renderUsing($this->output);

        render((string) $this->compile($view, $data), $verbosity);
    }

    /**
     * Compile the given view contents.
     *
     * @param string $view
     * @param array $data
     */
    protected function compile($view, $data)
    {
        extract($data);

        ob_start();

        include __DIR__ . "/../../resources/views/components/{$view}.php";

        return tap(ob_get_contents(), fn () => ob_end_clean());
    }

    /**
     * Mutates the given data with the given set of mutators.
     *
     * @param array<int, string>|string $data
     * @param array<int, callable(string): string> $mutators
     * @return array<int, string>|string
     */
    protected function mutate($data, $mutators)
    {
        foreach ($mutators as $mutator) {
            if (is_iterable($data)) {
                foreach ($data as $key => $value) {
                    $data[$key] = app($mutator)->__invoke($value);
                }
            } else {
                $data = app($mutator)->__invoke($data);
            }
        }

        return $data;
    }

    /**
     * Eventually performs a question using the component's question helper.
     *
     * @param callable $callable
     * @return mixed
     */
    protected function usingQuestionHelper($callable)
    {
        $property = with(new ReflectionClass(SymfonyStyle::class))
            // ->getParentClass()
            ->getProperty('questionHelper');

        $property->setAccessible(true);

        $currentHelper = $property->isInitialized($this->output)
            ? $property->getValue($this->output)
            : new SymfonyQuestionHelper();

        $property->setValue($this->output, new QuestionHelper());

        try {
            return $callable();
        } finally {
            $property->setValue($this->output, $currentHelper);
        }
    }
}
