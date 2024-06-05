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
use Hyperf\ViewEngine\Contract\FactoryInterface;

class MarkdownFactory
{
    public function __construct(
        protected readonly ConfigInterface $config,
        protected readonly FactoryInterface $factory
    ) {
    }

    public function __invoke()
    {
        return new Markdown($this->factory, $this->config->get('mail.markdown', []));
    }
}
