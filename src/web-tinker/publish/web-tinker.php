<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use function Hyperf\Support\env;

return [
    /*
     * The web tinker page will be available on this path.
     */
    'path' => '/tinker',

    /*
     * The controller and action that will be used to render the web tinker page.
     */
    'server' => 'http',

    /*
     * Possible values are 'auto', 'light' and 'dark'.
     */
    'theme' => 'auto',

    /*
     * By default this package will only run in local development.
     * Do not change this, unless you know what you are doing.
     */
    'enabled' => env('APP_ENV') === 'local',

    /*
    * This class can modify the output returned by Tinker. You can replace this with
    * any class that implements \FriendsOfHyperf\WebTinker\OutputModifiers\OutputModifier.
    */
    'output_modifier' => FriendsOfHyperf\WebTinker\OutputModifiers\PrefixDateTime::class,

    /*
    * These middleware will be assigned to every WebTinker route, giving you the chance
    * to add your own middlewares to this list or change any of the existing middleware.
    */
    'middleware' => [
        FriendsOfHyperf\WebTinker\Middleware\Authorize::class,
    ],

    /*
     * If you want to fine-tune PsySH configuration specify
     * configuration file name, relative to the root of your
     * application directory.
     */
    'config_file' => env('PSYSH_CONFIG', null),
];
