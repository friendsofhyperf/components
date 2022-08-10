<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\ConsoleSpinner;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @mixin \Symfony\Component\Console\Helper\ProgressBar
 */
class Spinner
{
    protected array $chars = ['⠏', '⠛', '⠹', '⢸', '⣰', '⣤', '⣆', '⡇'];

    protected ProgressBar $progressBar;

    protected int $step = 0;

    public function __construct(SymfonyStyle $output, int $max = 0, ?array $chars = null)
    {
        $this->chars = $chars ?? $this->chars;
        $this->progressBar = $output->createProgressBar($max);
        $this->progressBar->setBarCharacter('✔');
        $this->progressBar->setProgressCharacter($this->chars[0]);
        $this->progressBar->setMessage('');
        $this->progressBar->setFormat('%bar% %message%');
        $this->progressBar->setBarWidth(1);
        $this->progressBar->setRedrawFrequency(31);
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->progressBar, $name], $arguments);
    }

    public function getOriginalProgressBar(): ProgressBar
    {
        return $this->progressBar;
    }

    public function advance(int $step = 1): void
    {
        $this->step += $step;
        $this->progressBar->setProgressCharacter($this->chars[$this->step % count($this->chars)]);
        $this->progressBar->advance($step);
    }
}
