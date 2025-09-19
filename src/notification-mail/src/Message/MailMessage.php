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

use FriendsOfHyperf\Mail\Attachment;
use FriendsOfHyperf\Mail\Contract\Attachable;
use FriendsOfHyperf\Mail\Contract\Mailer;
use FriendsOfHyperf\Mail\Markdown;
use FriendsOfHyperf\Support\Contract\Htmlable;
use Hyperf\Conditionable\Conditionable;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\Arrayable;
use Hyperf\ViewEngine\Contract\Renderable;

use function Hyperf\Collection\collect;

class MailMessage extends SimpleMessage implements Renderable
{
    use Conditionable;

    /**
     * The view to be rendered.
     */
    public null|array|string $view = null;

    /**
     * The view data for the message.
     */
    public array $viewData = [];

    /**
     * The Markdown template to render (if applicable).
     */
    public ?string $markdown = 'notifications::email';

    /**
     * The current theme being used when generating emails.
     */
    public ?string $theme = null;

    /**
     * The "from" information for the message.
     */
    public array $from = [];

    /**
     * The "reply to" information for the message.
     */
    public array $replyTo = [];

    /**
     * The "cc" information for the message.
     */
    public array $cc = [];

    /**
     * The "bcc" information for the message.
     */
    public array $bcc = [];

    /**
     * The attachments for the message.
     */
    public array $attachments = [];

    /**
     * The raw attachments for the message.
     */
    public array $rawAttachments = [];

    /**
     * The tags for the message.
     */
    public array $tags = [];

    /**
     * The metadata for the message.
     */
    public array $metadata = [];

    /**
     * Priority level of the message.
     */
    public ?int $priority = null;

    /**
     * The callbacks for the message.
     */
    public array $callbacks = [];

    /**
     * Set the view for the mail message.
     */
    public function view(array|string $view, array $data = []): static
    {
        $this->view = $view;
        $this->viewData = $data;

        $this->markdown = null;

        return $this;
    }

    /**
     * Set the plain text view for the mail message.
     */
    public function text(string $textView, array $data = []): static
    {
        return $this->view([
            'html' => is_array($this->view) ? ($this->view['html'] ?? null) : $this->view,
            'text' => $textView,
        ], $data);
    }

    /**
     * Set the Markdown template for the notification.
     */
    public function markdown(string $view, array $data = []): static
    {
        $this->markdown = $view;
        $this->viewData = $data;

        $this->view = null;

        return $this;
    }

    /**
     * Set the default markdown template.
     */
    public function template(string $template): static
    {
        $this->markdown = $template;

        return $this;
    }

    /**
     * Set the theme to use with the Markdown template.
     */
    public function theme(string $theme): static
    {
        $this->theme = $theme;

        return $this;
    }

    /**
     * Set the from address for the mail message.
     */
    public function from(string $address, ?string $name = null): static
    {
        $this->from = [$address, $name];

        return $this;
    }

    /**
     * Set the "reply to" address of the message.
     */
    public function replyTo(array|string $address, ?string $name = null): static
    {
        if ($this->arrayOfAddresses($address)) {
            $this->replyTo += $this->parseAddresses($address);
        } else {
            $this->replyTo[] = [$address, $name];
        }

        return $this;
    }

    /**
     * Set the cc address for the mail message.
     */
    public function cc(array|string $address, ?string $name = null): static
    {
        if ($this->arrayOfAddresses($address)) {
            $this->cc += $this->parseAddresses($address);
        } else {
            $this->cc[] = [$address, $name];
        }

        return $this;
    }

    /**
     * Set the bcc address for the mail message.
     */
    public function bcc(array|string $address, ?string $name = null): static
    {
        if ($this->arrayOfAddresses($address)) {
            $this->bcc += $this->parseAddresses($address);
        } else {
            $this->bcc[] = [$address, $name];
        }

        return $this;
    }

    /**
     * Attach a file to the message.
     */
    public function attach(string|Attachable|Attachment $file, array $options = []): static
    {
        if ($file instanceof Attachable) {
            $file = $file->toMailAttachment();
        }

        if ($file instanceof Attachment) {
            return $file->attachTo($this);
        }

        $this->attachments[] = compact('file', 'options');

        return $this;
    }

    /**
     * Attach multiple files to the message.
     *
     * @param array<string|Attachable|Attachment|array> $files
     */
    public function attachMany(array $files): static
    {
        foreach ($files as $file => $options) {
            if (is_int($file)) {
                $this->attach($options);
            } else {
                $this->attach($file, $options);
            }
        }

        return $this;
    }

    /**
     * Attach in-memory data as an attachment.
     */
    public function attachData(string $data, string $name, array $options = []): static
    {
        $this->rawAttachments[] = compact('data', 'name', 'options');

        return $this;
    }

    /**
     * Add a tag header to the message when supported by the underlying transport.
     */
    public function tag(string $value): static
    {
        $this->tags[] = $value;
        return $this;
    }

    /**
     * Add a metadata header to the message when supported by the underlying transport.
     */
    public function metadata(string $key, string|int $value): static
    {
        $this->metadata[$key] = $value;

        return $this;
    }

    /**
     * Set the priority of this message.
     *
     * The value is an integer where 1 is the highest priority and 5 is the lowest.
     */
    public function priority(int $level): static
    {
        $this->priority = $level;

        return $this;
    }

    /**
     * Get the data array for the mail message.
     */
    public function data(): array
    {
        return array_merge($this->toArray(), $this->viewData);
    }

    /**
     * Render the mail notification message into an HTML string.
     */
    public function render(): Htmlable|\Hyperf\ViewEngine\Contract\Htmlable
    {
        $container = ApplicationContext::getContainer();
        if (isset($this->view)) {
            return $container->get(Mailer::class)->render(
                $this->view,
                $this->data()
            );
        }

        $markdown = $container->get(Markdown::class);

        return $markdown->theme($this->theme ?: $markdown->getTheme())
            ->render($this->markdown, $this->data());
    }

    /**
     * Register a callback to be called with the Symfony message instance.
     */
    public function withSymfonyMessage(callable $callback): static
    {
        $this->callbacks[] = $callback;

        return $this;
    }

    /**
     * Parse the multi-address array into the necessary format.
     */
    protected function parseAddresses(array $value): array
    {
        return collect($value)->map(function ($address, $name) {
            return [$address, is_numeric($name) ? null : $name];
        })->values()->all();
    }

    /**
     * Determine if the given "address" is actually an array of addresses.
     */
    protected function arrayOfAddresses(mixed $address): bool
    {
        return is_iterable($address) || $address instanceof Arrayable;
    }
}
