<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace Hyperf\HttpServer;

class Request
{
    /**
     * Get an array of all of the files on the request.
     *
     * @return array
     */
    public function allFiles()
    {
    }

    /**
     * Determine if the request contains a non-empty value for any of the given inputs.
     *
     * @param array|string $keys
     * @return bool
     */
    public function anyFilled($keys)
    {
    }

    /**
     * Retrieve input as a boolean value.
     *
     * Returns true when value is "1", "true", "on", and "yes". Otherwise, returns false.
     *
     * @param null|string $key
     * @param bool $default
     * @return bool
     */
    public function boolean($key = null, $default = false)
    {
    }

    /**
     * Retrieve input from the request as a collection.
     *
     * @param null|array|string $key
     * @return \Hyperf\Utils\Collection
     */
    public function collect($key = null)
    {
    }

    /**
     * Retrieve input from the request as a Carbon instance.
     *
     * @param string $key
     * @param null|string $format
     * @param null|string $tz
     * @return null|\Carbon\Carbon
     */
    public function date($key, $format = null, $tz = null)
    {
    }

    /**
     * Get all of the input except for a specified array of items.
     *
     * @param array|mixed $keys
     * @return array
     */
    public function except($keys)
    {
    }

    /**
     * Determine if the request contains a non-empty value for an input item.
     *
     * @param array|string $key
     * @return bool
     */
    public function filled($key)
    {
    }

    /**
     * Determine if the request contains any of the given inputs.
     *
     * @param array|string $keys
     * @return bool
     */
    public function hasAny($keys)
    {
    }

    /**
     * Determine if the given input key is an empty string for "has".
     *
     * @param string $key
     * @return bool
     */
    public function isEmptyString($key)
    {
    }

    /**
     * Determine if the request contains an empty value for an input item.
     *
     * @param array|string $key
     * @return bool
     */
    public function isNotFilled($key)
    {
    }

    /**
     * Get the keys for all of the input and files.
     *
     * @return array
     */
    public function keys()
    {
    }

    /**
     * Get the host name.
     *
     * @return string
     */
    public function host()
    {
    }

    /**
     * Get the HTTP host being requested.
     *
     * @return string
     */
    public function httpHost()
    {
    }

    /**
     * Get the scheme and HTTP host.
     *
     * @return string
     */
    public function schemeAndHttpHost()
    {
    }

    /**
     * Determine if the request is missing a given input item key.
     *
     * @param array|string $key
     * @return bool
     */
    public function missing($key)
    {
    }

    /**
     * Get a subset containing the provided keys with values from the input data.
     *
     * @param array|mixed $keys
     * @return array
     */
    public function only($keys)
    {
    }

    /**
     * Apply the callback if the request contains a non-empty value for the given input item key.
     *
     * @param string $key
     * @return $this|mixed
     */
    public function whenFilled($key, callable $callback, callable $default = null)
    {
    }

    /**
     * Apply the callback if the request contains the given input item key.
     *
     * @param string $key
     * @return $this|mixed
     */
    public function whenHas($key, callable $callback, callable $default = null)
    {
    }

    /**
     * Determine if the request is sending JSON.
     *
     * @return bool
     */
    public function isJson()
    {
    }
}
