<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\CommandValidation\Aspect;

use FriendsOfHyperf\CommandValidation\Concerns\ValidatesInput;
use FriendsOfHyperf\CommandValidation\ValidationException;
use Hyperf\Command\Command;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;

use function Hyperf\Support\class_uses_recursive;

/**
 * @method void validateInput()
 * @method void displayFailedValidationErrors(ValidatorInterface $validator)
 */
class ValidationAspect extends AbstractAspect
{
    public array $classes = [
        Command::class . '::execute',
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $command = $proceedingJoinPoint->getInstance();

        if (in_array(ValidatesInput::class, class_uses_recursive($command))) {
            try {
                (fn () => $this->validateInput())->call($command);
            } catch (ValidationException $e) {
                (fn () => $this->displayFailedValidationErrors($e->getValidator()))->call($command);

                return 1;
            }
        }

        return $proceedingJoinPoint->process();
    }
}
