<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\CommandValidation\Traits;

use Hyperf\Context\ApplicationContext;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Validation\ValidationException;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

trait ValidatesInput
{
    /**
     * The rules to use for input validation.
     */
    protected function rules(): array
    {
        return [];
    }

    /**
     * The custom error messages to use for input validation.
     */
    protected function messages(): array
    {
        return [];
    }

    /**
     * The custom attribute names to use for input validation.
     */
    protected function attributes(): array
    {
        return [];
    }

    /**
     * Validate the input as defined by the command.
     *
     * @throws ValidationException
     */
    protected function setUpValidatesInput(InputInterface $input, OutputInterface $output): void
    {
        if (empty($rules = $this->rules())) {
            return;
        }

        $container = ApplicationContext::getContainer();

        if (! $container->has(ValidatorFactoryInterface::class)) {
            throw new RuntimeException('Please install hyperf/validation to use the validate method.');
        }

        $validator = $container->get(ValidatorFactoryInterface::class)->make(
            array_merge($input->getArguments(), $input->getOptions()),
            $rules,
            $this->messages(),
            $this->attributes()
        );

        $validator->validate();
    }
}
