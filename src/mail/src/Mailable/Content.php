<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Mail\Mailable;

use Hyperf\Conditionable\Conditionable;

class Content
{
    use Conditionable;

    /**
     * The Blade view that should be rendered for the mailable.
     *
     * @var null|string
     */
    public $view;

    /**
     * The Blade view that should be rendered for the mailable.
     *
     * Alternative syntax for "view".
     *
     * @var null|string
     */
    public $html;

    /**
     * The Blade view that represents the text version of the message.
     *
     * @var null|string
     */
    public $text;

    /**
     * The Blade view that represents the Markdown version of the message.
     *
     * @var null|string
     */
    public $markdown;

    /**
     * The pre-rendered HTML of the message.
     *
     * @var null|string
     */
    public $htmlString;

    /**
     * The message's view data.
     *
     * @var array
     */
    public $with;

    /**
     * Create a new content definition.
     *
     * @param null|string $markdown
     *
     * @named-arguments-supported
     */
    public function __construct(?string $view = null, ?string $html = null, ?string $text = null, $markdown = null, array $with = [], ?string $htmlString = null)
    {
        $this->view = $view;
        $this->html = $html;
        $this->text = $text;
        $this->markdown = $markdown;
        $this->with = $with;
        $this->htmlString = $htmlString;
    }

    /**
     * Set the view for the message.
     *
     * @return $this
     */
    public function view(string $view)
    {
        $this->view = $view;

        return $this;
    }

    /**
     * Set the view for the message.
     *
     * @return $this
     */
    public function html(string $view)
    {
        return $this->view($view);
    }

    /**
     * Set the plain text view for the message.
     *
     * @return $this
     */
    public function text(string $view)
    {
        $this->text = $view;

        return $this;
    }

    /**
     * Set the Markdown view for the message.
     *
     * @return $this
     */
    public function markdown(string $view)
    {
        $this->markdown = $view;

        return $this;
    }

    /**
     * Set the pre-rendered HTML for the message.
     *
     * @return $this
     */
    public function htmlString(string $html)
    {
        $this->htmlString = $html;

        return $this;
    }

    /**
     * Add a piece of view data to the message.
     *
     * @param array|string $key
     * @param null|mixed $value
     * @return $this
     */
    public function with($key, $value = null)
    {
        if (is_array($key)) {
            $this->with = array_merge($this->with, $key);
        } else {
            $this->with[$key] = $value;
        }

        return $this;
    }
}
