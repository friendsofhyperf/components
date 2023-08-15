<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace Sentry;

use Hyperf\Context\Context;
use Sentry\State\Hub;
use Sentry\State\HubInterface;

use function Hyperf\Tappable\tap;

/**
 * @see \Sentry\SentrySdk
 */
class SentrySdk
{
    /**
     * @var HubInterface|null The current hub
     */
    private static $currentHub;

    /**
     * Constructor.
     */
    private function __construct()
    {
    }

    /**
     * Initializes the SDK by creating a new hub instance each time this method
     * gets called.
     */
    public static function init(): HubInterface
    {
        return tap(new Hub(), fn ($hub) => Context::set(__CLASS__, $hub));
    }

    /**
     * Gets the current hub. If it's not initialized then creates a new instance
     * and sets it as current hub.
     */
    public static function getCurrentHub(): HubInterface
    {
        return Context::getOrSet(__CLASS__, fn () => new Hub());
    }

    /**
     * Sets the current hub.
     *
     * @param HubInterface $hub The hub to set
     */
    public static function setCurrentHub(HubInterface $hub): HubInterface
    {
        return tap($hub, fn ($hub) => Context::set(__CLASS__, $hub));
    }
}
