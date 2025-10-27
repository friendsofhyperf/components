<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
(function () {
    /** @var null|\Composer\Autoload\ClassLoader $classLoader */
    $classLoader = null;
    $autoloadFiles = [
        __DIR__ . '/../../vendor/autoload.php',
        __DIR__ . '/../../../vendor/autoload.php',
        __DIR__ . '/../../../../vendor/autoload.php',
    ];
    foreach ($autoloadFiles as $autoloadFile) {
        if (file_exists($autoloadFile)) {
            $classLoader = require $autoloadFile;
            break;
        }
    }
    if (! $classLoader instanceof Composer\Autoload\ClassLoader) {
        return;
    }
    if ($file = $classLoader->findFile(PHPUnit\Framework\TestCase::class)) {
        $content = file_get_contents($file);
        $replace = 'public function runBare';
        if (strpos($content, $find = 'final ' . $replace) !== false) {
            $content = str_replace($find, $replace, $content);
            file_put_contents($file, $content);
        }
    }
})();
