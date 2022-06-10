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

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

#[Attribute(Attribute::TARGET_METHOD)]
class Validation extends AbstractAnnotation
{
    public function __construct(public array $rules = [], public array $messages = [], public string $formRequest = '', public string $scene = '', public bool $resolve = true)
    {
    }
}
