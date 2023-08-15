<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\CommandValidation\Concerns;

use FriendsOfHyperf\CommandValidation\ValidationException;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ValidatorInterface;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;

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
    protected function validateInput(): void
    {
        if (empty($rules = $this->rules())) {
            return;
        }

        $container = ApplicationContext::getContainer();

        $validator = $container->get(ValidatorFactoryInterface::class)->make(
            array_merge($this->input->getArguments(), $this->input->getOptions()),
            $rules,
            $this->messages(),
            $this->attributes()
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Display the validation errors for the given validator.
     */
    protected function displayFailedValidationErrors(ValidatorInterface $validator): void
    {
        foreach ($validator->errors()->all() as $error) {
            $this->output->error($error);
        }
    }
}
