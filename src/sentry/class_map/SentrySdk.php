<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/2.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace Sentry;

use Hyperf\Context\Context;
use Sentry\State\Hub;
use Sentry\State\HubInterface;

/**
 * @see \Sentry\SentrySdk
 */
class SentrySdk
{
    /**
     * @var null|HubInterface The current hub
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
        return tap(new Hub(), function ($hub) {
            return Context::set(__CLASS__, $hub);
        });
    }

    /**
     * Gets the current hub. If it's not initialized then creates a new instance
     * and sets it as current hub.
     */
    public static function getCurrentHub(): HubInterface
    {
        return Context::getOrSet(__CLASS__, function () {
            return new Hub();
        });
    }

    /**
     * Sets the current hub.
     *
     * @param HubInterface $hub The hub to set
     */
    public static function setCurrentHub(HubInterface $hub): HubInterface
    {
        return tap($hub, function ($hub) {
            return Context::set(__CLASS__, $hub);
        });
    }
}
