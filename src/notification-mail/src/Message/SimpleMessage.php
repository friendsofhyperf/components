<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Notification\Mail\Message;

use FriendsOfHyperf\Notification\Mail\Action;
use FriendsOfHyperf\Support\Contract\Htmlable;

class SimpleMessage
{
    /**
     * The "level" of the notification (info, success, error).
     */
    public string $level = 'info';

    /**
     * The subject of the notification.
     */
    public ?string $subject = null;

    /**
     * The notification's greeting.
     */
    public ?string $greeting = null;

    /**
     * The notification's salutation.
     */
    public ?string $salutation = null;

    /**
     * The "intro" lines of the notification.
     */
    public array $introLines = [];

    /**
     * The "outro" lines of the notification.
     */
    public array $outroLines = [];

    /**
     * The text / label for the action.
     */
    public ?string $actionText = null;

    /**
     * The action URL.
     */
    public ?string $actionUrl = null;

    /**
     * The name of the mailer that should send the notification.
     */
    public ?string $mailer = null;

    /**
     * Indicate that the notification gives information about a successful operation.
     */
    public function success(): static
    {
        $this->level = 'success';

        return $this;
    }

    /**
     * Indicate that the notification gives information about an error.
     */
    public function error(): static
    {
        $this->level = 'error';

        return $this;
    }

    /**
     * Set the "level" of the notification (success, error, etc.).
     */
    public function level(string $level): static
    {
        $this->level = $level;

        return $this;
    }

    /**
     * Set the subject of the notification.
     */
    public function subject(string $subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Set the greeting of the notification.
     */
    public function greeting(string $greeting): static
    {
        $this->greeting = $greeting;

        return $this;
    }

    /**
     * Set the salutation of the notification.
     */
    public function salutation(string $salutation): static
    {
        $this->salutation = $salutation;

        return $this;
    }

    /**
     * Add a line of text to the notification.
     */
    public function line(mixed $line): static
    {
        return $this->with($line);
    }

    /**
     * Add a line of text to the notification if the given condition is true.
     */
    public function lineIf(bool $boolean, mixed $line): static
    {
        if ($boolean) {
            return $this->line($line);
        }

        return $this;
    }

    /**
     * Add lines of text to the notification.
     */
    public function lines(iterable $lines): static
    {
        foreach ($lines as $line) {
            $this->line($line);
        }

        return $this;
    }

    /**
     * Add lines of text to the notification if the given condition is true.
     */
    public function linesIf(bool $boolean, iterable $lines): static
    {
        if ($boolean) {
            return $this->lines($lines);
        }

        return $this;
    }

    /**
     * Add a line of text to the notification.
     */
    public function with(mixed $line): static
    {
        if ($line instanceof Action) {
            $this->action($line->text, $line->url);
        } elseif (! $this->actionText) {
            $this->introLines[] = $this->formatLine($line);
        } else {
            $this->outroLines[] = $this->formatLine($line);
        }

        return $this;
    }

    /**
     * Configure the "call to action" button.
     */
    public function action(string $text, string $url): static
    {
        $this->actionText = $text;
        $this->actionUrl = $url;

        return $this;
    }

    /**
     * Set the name of the mailer that should send the notification.
     */
    public function mailer(string $mailer): static
    {
        $this->mailer = $mailer;

        return $this;
    }

    /**
     * Get an array representation of the message.
     */
    public function toArray(): array
    {
        return [
            'level' => $this->level,
            'subject' => $this->subject,
            'greeting' => $this->greeting,
            'salutation' => $this->salutation,
            'introLines' => $this->introLines,
            'outroLines' => $this->outroLines,
            'actionText' => $this->actionText,
            'actionUrl' => $this->actionUrl,
            'displayableActionUrl' => str_replace(['mailto:', 'tel:'], '', $this->actionUrl ?? ''),
        ];
    }

    /**
     * Format the given line of text.
     */
    protected function formatLine(Htmlable|\Hyperf\ViewEngine\Contract\Htmlable|array|string $line): \Hyperf\ViewEngine\Contract\Htmlable|Htmlable|string
    {
        if ($line instanceof Htmlable || $line instanceof \Hyperf\ViewEngine\Contract\Htmlable) {
            return $line;
        }

        if (is_array($line)) {
            return implode(' ', array_map('trim', $line));
        }

        return trim(implode(' ', array_map('trim', preg_split('/\r\n|\r|\n/', $line))));
    }
}
