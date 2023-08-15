<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace Hyperf\HttpServer\Contract;

interface RequestInterface
{
    /**
     * Get an array of all of the files on the request.
     */
    public function allFiles(): array;

    /**
     * Determine if the request contains a non-empty value for any of the given inputs.
     *
     * @param array|string $keys
     */
    public function anyFilled($keys): bool;

    /**
     * Retrieve input as a boolean value.
     *
     * Returns true when value is "1", "true", "on", and "yes". Otherwise, returns false.
     *
     * @param string|null $key
     * @param bool $default
     */
    public function boolean($key = null, $default = false): bool;

    /**
     * Retrieve input from the request as a collection.
     *
     * @param array|string|null $key
     */
    public function collect(array|string|null $key = null): \Hyperf\Collection\Collection;

    /**
     * Retrieve input from the request as a Carbon instance.
     */
    public function date(string $key, ?string $format = null, ?string $tz = null): ?\Carbon\Carbon;

    /**
     * Get all of the input except for a specified array of items.
     *
     * @param array|mixed $keys
     */
    public function except($keys): array;

    /**
     * Determine if the request contains a non-empty value for an input item.
     */
    public function filled(array|string $key): bool;

    /**
     * Determine if the request contains any of the given inputs.
     */
    public function hasAny(array|string $keys): bool;

    /**
     * Determine if the given input key is an empty string for "has".
     */
    public function isEmptyString(string $key): bool;

    /**
     * Determine if the request contains an empty value for an input item.
     */
    public function isNotFilled(array|string $key): bool;

    /**
     * Get the keys for all of the input and files.
     */
    public function keys(): array;

    /**
     * Get the host name.
     */
    public function host(): string;

    public function getHost(): string;

    /**
     * Get the HTTP host being requested.
     */
    public function httpHost(): string;

    public function getHttpHost(): string;

    public function getPort(): int;

    public function getScheme(): string;

    public function isSecure(): bool;

    public function getSchemeAndHttpHost(): string;

    /**
     * Get the scheme and HTTP host.
     */
    public function schemeAndHttpHost(): string;

    /**
     * Merge new input into the current request's input array.
     *
     * @return $this
     */
    public function merge(array $input): self;

    /**
     * Merge new input into the request's input, but only when that key is missing from the request.
     *
     * @return $this
     */
    public function mergeIfMissing(array $input): self;

    /**
     * Determine if the request is missing a given input item key.
     *
     * @param array|string $key
     */
    public function missing($key): bool;

    /**
     * Get a subset containing the provided keys with values from the input data.
     *
     * @param array|mixed $keys
     */
    public function only($keys): array;

    /**
     * Determine if the current request is asking for JSON.
     */
    public function wantsJson(): bool;

    /**
     * Apply the callback if the request contains a non-empty value for the given input item key.
     *
     * @return $this|mixed
     */
    public function whenFilled(string $key, callable $callback, callable $default = null);

    /**
     * Apply the callback if the request contains the given input item key.
     *
     * @return $this|mixed
     */
    public function whenHas(string $key, callable $callback, callable $default = null);

    /**
     * Determine if the request is sending JSON.
     */
    public function isJson(): bool;

    /**
     * Retrieve input from the request as an enum.
     *
     * @template TEnum
     *
     * @param string $key
     * @param class-string<TEnum> $enumClass
     * @return TEnum|null
     */
    public function enum($key, $enumClass);

    /**
     * Determine if the request contains a given input item key.
     *
     * @param array|string $key
     */
    public function exists($key): bool;

    /**
     * Retrieve input from the request as a Stringable instance.
     *
     * @param string $key
     * @param mixed $default
     * @return \Hyperf\Stringable\Stringable
     */
    public function str($key, $default = null);

    /**
     * Retrieve input from the request as a Stringable instance.
     *
     * @param string $key
     * @param mixed $default
     * @return \Hyperf\Stringable\Stringable
     */
    public function string($key, $default = null);

    /**
     * Retrieve input as an integer value.
     *
     * @param string $key
     * @param int $default
     */
    public function integer($key, $default = 0): int;
}
