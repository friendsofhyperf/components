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
    /** @var \Composer\Autoload\ClassLoader $classLoader */
    $classLoader = require __DIR__ . '/../vendor/autoload.php';
    if ($file = $classLoader->findFile(PHPUnit\Framework\TestCase::class)) {
        $content = file_get_contents($file);
        $replace = 'public function runBare';
        if (strpos($content, $find = 'final ' . $replace) !== false) {
            $content = str_replace($find, $replace, $content);
            file_put_contents($file, $content);
        }
    }
})();
