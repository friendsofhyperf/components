<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Mail\Factory;

use FriendsOfHyperf\Mail\Markdown;
use Hyperf\Contract\ConfigInterface;

class MarkdownFactory
{
    public function __construct(
        protected ConfigInterface $config
    ) {
    }

    public function __invoke()
    {
        return new Markdown($this->config->get('mail.markdown.theme', 'default'), $this->config->get('mail.markdown.paths', []));
    }
}
