<?php

declare(strict_types=1);
/**
 * This file is part of grpc-validation.
 *
 * @link     https://github.com/friendofhyperf/grpc-validation
 * @document https://github.com/friendofhyperf/grpc-validation/blob/main/README.md
 * @contact  huangdijia@gmail.com
 * @license  https://github.com/friendofhyperf/grpc-validation/blob/main/LICENSE
 */
namespace FriendsOfHyperf\GrpcValidation\Annotation;

class ValidationException extends \Exception
{
    public function __construct(string $message, int $code = 422, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
