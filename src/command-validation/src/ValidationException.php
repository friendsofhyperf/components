<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\CommandValidation;

use Exception;
use Hyperf\Contract\ValidatorInterface;

class ValidationException extends Exception
{
    /**
     * Create a new exception instance.
     */
    public function __construct(public ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Return the validator instance.
     */
    public function getValidator(): ValidatorInterface
    {
        return $this->validator;
    }
}
