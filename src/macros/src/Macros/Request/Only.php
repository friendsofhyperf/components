<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Macros\Macros\Request;

use Hyperf\Utils\Arr;
use stdClass;

/**
 * @mixin \Hyperf\HttpServer\Request
 */
class Only
{
    public function __invoke()
    {
        return function ($keys) {
            $results = [];

            $input = $this->all();

            $placeholder = new stdClass();

            foreach (is_array($keys) ? $keys : func_get_args() as $key) {
                $value = data_get($input, $key, $placeholder);

                if ($value !== $placeholder) {
                    Arr::set($results, $key, $value);
                }
            }

            return $results;
        };
    }
}
