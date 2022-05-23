<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/macros.
 *
 * @link     https://github.com/friendsofhyperf/macros
 * @document https://github.com/friendsofhyperf/macros/blob/2.x/README.md
 * @contact  huangdijia@gmail.com
 */
use Carbon\Carbon;
use Hyperf\HttpServer\Request;
use Hyperf\Utils\Arr;
use Hyperf\Utils\Str;

if (! Request::hasMacro('allFiles')) {
    Request::macro('allFiles', fn () => $this->getUploadedFiles());
}

if (! Request::hasMacro('anyFilled')) {
    Request::macro('anyFilled', function ($keys) {
        $keys = is_array($keys) ? $keys : func_get_args();

        foreach ($keys as $key) {
            if ($this->filled($key)) {
                return true;
            }
        }

        return false;
    });
}

if (! Request::hasMacro('boolean')) {
    Request::macro('boolean', fn ($key = null, $default = false) => filter_var($this->input($key, $default), FILTER_VALIDATE_BOOLEAN));
}

if (! Request::hasMacro('collect')) {
    Request::macro('collect', fn ($key = null) => collect(is_array($key) ? $this->only($key) : $this->input($key)));
}

if (! Request::hasMacro('date')) {
    Request::macro('date', function ($key, $format = null, $tz = null) {
        if ($this->isNotFilled($key)) {
            return null;
        }

        if (is_null($format)) {
            return Carbon::parse($this->input($key), $tz);
        }

        return Carbon::createFromFormat($format, $this->input($key), $tz);
    });
}

if (! Request::hasMacro('except')) {
    Request::macro('except', function ($keys) {
        $keys = is_array($keys) ? $keys : func_get_args();

        $results = $this->all();

        Arr::forget($results, $keys);

        return $results;
    });
}

if (! Request::hasMacro('filled')) {
    Request::macro('filled', function ($key) {
        $keys = is_array($key) ? $key : func_get_args();

        foreach ($keys as $value) {
            if ($this->isEmptyString($value)) {
                return false;
            }
        }

        return true;
    });
}

if (! Request::hasMacro('hasAny')) {
    Request::macro('hasAny', function ($keys) {
        $keys = is_array($keys) ? $keys : func_get_args();

        $input = $this->all();

        return Arr::hasAny($input, $keys);
    });
}

if (! Request::hasMacro('isEmptyString')) {
    Request::macro('isEmptyString', function ($key) {
        $value = $this->input($key);

        return ! is_bool($value) && ! is_array($value) && trim((string) $value) === '';
    });
}

if (! Request::hasMacro('isNotFilled')) {
    Request::macro('isNotFilled', function ($key) {
        $keys = is_array($key) ? $key : func_get_args();

        foreach ($keys as $value) {
            if (! $this->isEmptyString($value)) {
                return false;
            }
        }

        return true;
    });
}

if (! Request::hasMacro('keys')) {
    Request::macro('keys', fn () => array_merge(array_keys($this->all()), array_keys($this->getUploadedFiles())));
}

if (! Request::hasMacro('missing')) {
    Request::macro('missing', function ($key) {
        $keys = is_array($key) ? $key : func_get_args();

        return ! $this->has($keys);
    });
}

if (! Request::hasMacro('only')) {
    Request::macro('only', function ($keys) {
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
    });
}

if (! Request::hasMacro('whenFilled')) {
    Request::macro('whenFilled', function ($key, callable $callback, callable $default = null) {
        if ($this->filled($key)) {
            return $callback(data_get($this->all(), $key)) ?: $this;
        }

        if ($default) {
            return $default();
        }

        return $this;
    });
}

if (! Request::hasMacro('whenHas')) {
    Request::macro('whenHas', function ($key, callable $callback, callable $default = null) {
        if ($this->has($key)) {
            return $callback(data_get($this->all(), $key)) ?: $this;
        }

        if ($default) {
            return $default();
        }

        return $this;
    });
}

if (! Request::hasMacro('isJson')) {
    Request::macro('isJson', fn () => Str::contains($this->header('CONTENT_TYPE') ?? '', ['/json', '+json']));
}
