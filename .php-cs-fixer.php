<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use Huangdijia\PhpCsFixer\Config;
use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

require __DIR__ . '/vendor/autoload.php';

return (new Config())
    ->setHeaderComment(
        projectName: 'friendsofhyperf/components',
        projectLink: 'https://github.com/friendsofhyperf/components',
        projectDocument: 'https://github.com/friendsofhyperf/components/blob/main/README.md',
        contacts: [
            'huangdijia@gmail.com',
        ],
    )
    ->setParallelConfig(ParallelConfigFactory::detect(filesPerProcess: 20))
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->exclude('bin')
            ->exclude('runtime')
            ->exclude('vendor')
            ->in(__DIR__)
            ->append([
                __FILE__,
            ])
    )
    ->setUsingCache(false);
