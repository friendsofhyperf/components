<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.0/README.md
 * @contact  huangdijia@gmail.com
 */
return [
    // see https://github.com/bobthecow/psysh/wiki/Config-options
    'usePcntl' => false,

    // Commands to include in the tinker shell.
    'command_white_list' => ['list', 'migrate'],

    // This option allows you to add additional commands that should be available within the Tinker environment. Once the command is in this array you may execute the command in Tinker using its name.
    'commands' => [
        // App\Console\Commands\ExampleCommand::class,
    ],

    'alias' => [
    ],

    'dont_alias' => [
    ],

    'casters' => [
        // CLASS => CasterCallback,
    ],
];
