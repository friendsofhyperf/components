<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Confd\DotEnv;

/**
 * MirazMac\DotEnv\Writer.
 *
 * A PHP library to write values to .env files.
 *
 * Heavily inspired from https://github.com/msztorc/laravel-env
 *
 * @see https://mirazmac.com
 */
class Writer
{
    /**
     * The .env file content.
     */
    protected string $content;

    /**
     * Path to the .env file.
     */
    protected string $path;

    /**
     * Parsed variables, just for reference, not properly type-casted.
     */
    protected array $variables = [];

    /**
     * Stores if a change was made.
     */
    protected bool $changed = false;

    /**
     * Constructs a new instance.
     *
     * @param string $path The environment path
     * @throws \LogicException If the file is missing
     */
    public function __construct(string $path)
    {
        if (! is_file($path)) {
            throw new \LogicException("No file exists at: {$path}");
        }

        $this->path = $path;
        $this->content = file_get_contents($path);
        $this->parse();
    }

    /**
     * Set the value of an environment variable, updated if exists, added if doesn't.
     *
     * @param string $key The key
     * @param string $value The value
     * @param bool $forceQuote By default the whether the value is wrapped
     *                         in double quotes is determined automatically.
     *                         However, you may wish to force quote a value
     *
     * @throws \InvalidArgumentException If a new key contains invalid characters
     */
    public function set(string $key, string $value, bool $forceQuote = false): self
    {
        $originalValue = $value;

        // Quote properly
        $value = $this->escapeValue($value, $forceQuote);

        // If the key exists, replace it's value
        if ($this->exists($key)) {
            $this->content = preg_replace("/^{$key}=.*$/mu", "{$key}={$value}", $this->content);
        } else {
            // otherwise append to the end
            if (! $this->isValidName($key)) {
                throw new \InvalidArgumentException("Failed to add new key `{$key}`. As it contains invalid characters, please use only ASCII letters, digits and underscores only.");
            }

            $this->content .= PHP_EOL . "{$key}={$value}" . PHP_EOL;
        }

        $this->variables[$key] = $originalValue;
        $this->changed = true;

        return $this;
    }

    /**
     * Set more values at once, downside of this is you can't set "forceQuote" specificly.
     *
     * @param array $values The values as key => value pairs
     */
    public function setValues(array $values, bool $forceQuote = false): self
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value, $forceQuote);
        }

        return $this;
    }

    /**
     * Delete an environment variable if present.
     *
     * @param string $key The key
     */
    public function delete(string $key): self
    {
        if ($this->exists($key)) {
            $this->content = preg_replace("/^{$key}=.*\\s{0,1}/mu", '', $this->content);
            unset($this->variables[$key]);
            $this->changed = true;
        }

        return $this;
    }

    /**
     * States if one or more values has changed.
     */
    public function hasChanged(): bool
    {
        return $this->changed;
    }

    /**
     * Returns the value for a variable is present.
     *
     * NOTE: This is a writer library so all values are parsed as string.
     * Don't use this as an way to read values from dot env files. Instead use something robust like:
     * https://github.com/vlucas/phpdotenv
     *
     * @param string $key The key
     */
    public function get(string $key): string
    {
        return $this->exists($key) ? $this->variables[$key] : '';
    }

    /**
     * Returns all the variables parsed.
     */
    public function getAll(): array
    {
        return $this->variables;
    }

    /**
     * Returns the current content.
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Write the contents to the env file.
     *
     * @param bool $force By default we only write when something has changed,
     *                    but you can force to write the file
     */
    public function write(bool $force = false): bool
    {
        // If nothing is changed don't bother writing unless forced
        if (! $this->hasChanged() && ! $force) {
            return true;
        }

        return file_put_contents($this->path, $this->content, \LOCK_EX) !== false ?? true;
    }

    /**
     * Check if a variable exists or not.
     *
     * @param string $key The key
     */
    public function exists(string $key): bool
    {
        return array_key_exists($key, $this->variables);
    }

    /**
     * Determines whether the specified key is valid name for .env files.
     *
     * @param string $key The key
     */
    protected function isValidName(string $key): bool
    {
        return preg_match('/^[\w\.]+$/', $key) ? true : false;
    }

    /**
     * Parses the environment file line by line and store the variables.
     */
    protected function parse(): void
    {
        $lines = preg_split('/\r\n|\r|\n/', $this->content);

        foreach ($lines as $line) {
            if (mb_strlen(trim($line)) && ! (mb_strpos(trim($line), '#') === 0)) {
                [$key, $value] = explode('=', (string) $line);
                $this->variables[$key] = $this->formatValue($value);
            }
        }
    }

    /**
     * Strips quotes from the values when reading.
     *
     * @param string $value The value
     */
    protected function stripQuotes(string $value): string
    {
        return preg_replace('/^(\'(.*)\'|"(.*)")$/u', '$2$3', $value);
    }

    /**
     * Formats the value for human friendly output.
     *
     * @param string $value The value
     */
    protected function formatValue(string $value): string
    {
        $value = trim(explode('#', trim($value))[0]);

        return stripslashes($this->stripQuotes($value));
    }

    /**
     * Escapes the value before writing to the contents.
     *
     * @param string $value The value
     * @param bool $forceQuote Whether force quoting is preferred
     */
    protected function escapeValue(string $value, bool $forceQuote): string
    {
        if (empty($value)) {
            return '';
        }

        // Quote the values if
        // it contains white-space or the following characters: " \ = : . $
        // or simply force quote is enabled
        if (preg_match('/\s|"|\\\\|=|:|\.|#|\$/u', $value) || $forceQuote) {
            // Replace backslashes with even more backslashes so when writing we can have escaped backslashes
            // damn.. that rhymes
            $value = str_replace('\\', '\\\\\\\\', $value);
            // Wrap the
            $value = '"' . addcslashes($value, '"') . '"';
        }

        return $value;
    }
}
