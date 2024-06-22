<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
return [
    /*
    |--------------------------------------------------------------------------
    | NAMESPACE
    |--------------------------------------------------------------------------
    |
    | The namespace where your DTOs will be created.
    |
    */

    'namespace' => 'App\DTO',

    /*
    |--------------------------------------------------------------------------
    | REQUIRE CASTING
    |--------------------------------------------------------------------------
    |
    | If this is set to true, you must configure a cast type for all properties of your DTOs.
    | If a property doesn't have a cast type configured it will throw a
    | \FriendsOfHyperf\ValidatedDTO\Exceptions\MissingCastTypeException exception
    |
    */
    'require_casting' => false,
];
