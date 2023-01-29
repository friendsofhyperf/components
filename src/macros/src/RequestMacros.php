<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Macros;

use Carbon\Carbon;
use Hyperf\Context\Context;
use Hyperf\HttpServer\Request;
use Hyperf\Utils\Arr;
use Hyperf\Utils\Str;
use stdClass;

/**
 * @mixin Request
 */
class RequestMacros
{
    public function allFiles()
    {
        return fn () => $this->getUploadedFiles();
    }

    public function anyFilled()
    {
        return function ($keys) {
            $keys = is_array($keys) ? $keys : func_get_args();

            foreach ($keys as $key) {
                /* @phpstan-ignore-next-line */
                if ($this->filled($key)) {
                    return true;
                }
            }

            return false;
        };
    }

    public function boolean()
    {
        return fn (string $key = '', $default = false) => filter_var($this->input($key, $default), FILTER_VALIDATE_BOOLEAN);
    }

    public function collect()
    {
        return function ($key = null) {
            if (is_null($key)) {
                return $this->all();
            }

            /* @phpstan-ignore-next-line */
            return collect(is_array($key) ? $this->only($key) : $this->input($key));
        };
    }

    public function date()
    {
        return function (string $key, $format = null, $tz = null) {
            /* @phpstan-ignore-next-line */
            if ($this->isNotFilled($key)) {
                return null;
            }

            if (is_null($format)) {
                return Carbon::parse($this->input($key), $tz);
            }

            return Carbon::createFromFormat($format, $this->input($key), $tz);
        };
    }

    public function enum()
    {
        return function ($key, $enumClass) {
            if (
                /* @phpstan-ignore-next-line */
                $this->isNotFilled($key)
                || ! function_exists('enum_exists')
                || ! enum_exists($enumClass)
                || ! method_exists($enumClass, 'tryFrom')
            ) {
                return null;
            }

            return $enumClass::tryFrom($this->input($key));
        };
    }

    public function except()
    {
        return function ($keys) {
            $keys = is_array($keys) ? $keys : func_get_args();
            $results = $this->all();

            Arr::forget($results, $keys);

            return $results;
        };
    }

    public function exists()
    {
        return fn ($key) => $this->has($key);
    }

    public function filled()
    {
        return function ($key) {
            $keys = is_array($key) ? $key : func_get_args();

            foreach ($keys as $value) {
                /* @phpstan-ignore-next-line */
                if ($this->isEmptyString($value)) {
                    return false;
                }
            }

            return true;
        };
    }

    public function float()
    {
        return fn ($key, $default = null) => (float) $this->input($key, $default);
    }

    public function hasAny()
    {
        return fn ($keys) => Arr::hasAny($this->all(), is_array($keys) ? $keys : func_get_args());
    }

    public function host()
    {
        /* @phpstan-ignore-next-line */
        return fn () => $this->getHttpHost();
    }

    public function httpHost()
    {
        return fn () => $this->getHeader('HOST')[0] ?? $this->getServerParams('SERVER_NAME')[0] ?? $this->getServerParams('SERVER_ADDR')[0] ?? '';
    }

    public function integer()
    {
        return fn ($key, $default = null) => (int) $this->input($key, $default);
    }

    public function isEmptyString()
    {
        return function ($key) {
            $value = $this->input($key);

            return ! is_bool($value) && ! is_array($value) && trim((string) $value) === '';
        };
    }

    public function isJson()
    {
        return fn () => Str::contains($this->header('CONTENT_TYPE') ?? '', ['/json', '+json']);
    }

    public function isNotFilled()
    {
        return function ($key) {
            $keys = is_array($key) ? $key : func_get_args();

            foreach ($keys as $value) {
                /* @phpstan-ignore-next-line */
                if (! $this->isEmptyString($value)) {
                    return false;
                }
            }

            return true;
        };
    }

    public function keys()
    {
        return fn () => array_merge(array_keys($this->all()), array_keys($this->getUploadedFiles()));
    }

    public function merge()
    {
        return function (array $input) {
            /* @phpstan-ignore-next-line */
            Context::override($this->contextkeys['parsedData'], fn ($inputs) => array_replace((array) $inputs, $input));

            return $this;
        };
    }

    public function mergeIfMissing()
    {
        /* @phpstan-ignore-next-line */
        return fn (array $input) => $this->merge(collect($input)->filter(fn ($value, $key) => $this->missing($key))->toArray());
    }

    public function missing()
    {
        return fn ($key) => ! $this->has(is_array($key) ? $key : func_get_args());
    }

    public function only()
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

    public function schemeAndHttpHost()
    {
        return function () {
            $https = $this->getServerParams('HTTPS')[0] ?? null;
            /* @phpstan-ignore-next-line */
            return ($https ? 'https' : 'http') . '://' . $this->httpHost();
        };
    }

    public function str()
    {
        return fn ($key, $default = null) => Str::of($this->input($key, $default));
    }

    public function string()
    {
        return fn ($key, $default = null) => Str::of($this->input($key, $default));
    }

    public function whenFilled()
    {
        return function ($key, callable $callback, callable $default = null) {
            /* @phpstan-ignore-next-line */
            if ($this->filled($key)) {
                return $callback(data_get($this->all(), $key)) ?: $this;
            }

            if ($default) {
                return $default();
            }

            return $this;
        };
    }

    public function whenHas()
    {
        return function ($key, callable $callback, callable $default = null) {
            if ($this->has($key)) {
                return $callback(data_get($this->all(), $key)) ?: $this;
            }

            if ($default) {
                return $default();
            }

            return $this;
        };
    }
}
