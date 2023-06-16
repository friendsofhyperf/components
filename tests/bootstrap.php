<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
require_once __DIR__ . '/../vendor/autoload.php';
use Hyperf\Context\ApplicationContext;
use Hyperf\Di\ClassLoader;
use Hyperf\Di\Container;
use Hyperf\Di\Definition\DefinitionSourceFactory;

defined('BASE_PATH') or define('BASE_PATH', dirname(__DIR__, 1));

(function () {
    ClassLoader::init();
    $container = new Container((new DefinitionSourceFactory())());

    ApplicationContext::setContainer($container);
})();
