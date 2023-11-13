<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Telescope;

use InvalidArgumentException;
use Stringable;

use function in_array;

use const E_COMPILE_ERROR;
use const E_COMPILE_WARNING;
use const E_CORE_ERROR;
use const E_CORE_WARNING;
use const E_DEPRECATED;
use const E_ERROR;
use const E_NOTICE;
use const E_PARSE;
use const E_RECOVERABLE_ERROR;
use const E_STRICT;
use const E_USER_DEPRECATED;
use const E_USER_ERROR;
use const E_USER_NOTICE;
use const E_USER_WARNING;
use const E_WARNING;

/**
 * This class represents an enum of severity levels an event can be associated
 * to.
 */
final class Severity implements Stringable
{
    /**
     * This constant represents the "debug" severity level.
     *
     * @internal
     */
    public const DEBUG = 'debug';

    /**
     * This constant represents the "info" severity level.
     *
     * @internal
     */
    public const INFO = 'info';

    /**
     * This constant represents the "warning" severity level.
     *
     * @internal
     */
    public const WARNING = 'warning';

    /**
     * This constant represents the "error" severity level.
     *
     * @internal
     */
    public const ERROR = 'error';

    /**
     * This constant represents the "fatal" severity level.
     *
     * @internal
     */
    public const FATAL = 'fatal';

    /**
     * This constant contains the list of allowed enum values.
     *
     * @internal
     */
    public const ALLOWED_SEVERITIES = [
        self::DEBUG,
        self::INFO,
        self::WARNING,
        self::ERROR,
        self::FATAL,
    ];

    /**
     * @var string The value of this enum instance
     */
    private string $value;

    /**
     * Constructor.
     *
     * @param string $value The value this instance represents
     */
    public function __construct(string $value = self::INFO)
    {
        if (! in_array($value, self::ALLOWED_SEVERITIES, true)) {
            throw new InvalidArgumentException(sprintf('The "%s" is not a valid enum value.', $value));
        }

        $this->value = $value;
    }

    /**
     * @see https://www.php.net/manual/en/language.oop5.magic.php#object.tostring
     */
    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * Translate a PHP Error constant into a Sentry log level group.
     *
     * @param int $severity PHP E_* error constant
     */
    public static function fromError(int $severity): self
    {
        switch ($severity) {
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
            case E_WARNING:
            case E_USER_WARNING:
                return self::warning();
            case E_ERROR:
            case E_PARSE:
            case E_CORE_ERROR:
            case E_CORE_WARNING:
            case E_COMPILE_ERROR:
            case E_COMPILE_WARNING:
                return self::fatal();
            case E_RECOVERABLE_ERROR:
            case E_USER_ERROR:
                return self::error();
            case E_NOTICE:
            case E_USER_NOTICE:
            case E_STRICT:
                return self::info();
            default:
                return self::error();
        }
    }

    /**
     * Creates a new instance of this enum for the "debug" value.
     */
    public static function debug(): self
    {
        return new self(self::DEBUG);
    }

    /**
     * Creates a new instance of this enum for the "info" value.
     */
    public static function info(): self
    {
        return new self(self::INFO);
    }

    /**
     * Creates a new instance of this enum for the "warning" value.
     */
    public static function warning(): self
    {
        return new self(self::WARNING);
    }

    /**
     * Creates a new instance of this enum for the "error" value.
     */
    public static function error(): self
    {
        return new self(self::ERROR);
    }

    /**
     * Creates a new instance of this enum for the "fatal" value.
     */
    public static function fatal(): self
    {
        return new self(self::FATAL);
    }

    /**
     * Returns whether two object instances of this class are equal.
     *
     * @param self $other The object to compare
     */
    public function isEqualTo(self $other): bool
    {
        return $this->value === (string) $other;
    }
}
