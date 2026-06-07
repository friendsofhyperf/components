# Command Validation

Validate a Hyperf command's arguments and options before the command runs.

## Installation

```shell
composer require friendsofhyperf/command-validation
```

The package requires Hyperf `~3.2.0`, including `hyperf/validation`, and declares
no optional dependencies. Hyperf discovers the component's empty
`ConfigProvider` automatically, so the component has no configuration to
publish.

## Usage

Add the `ValidatesInput` trait to a command and override `rules()`. You may also
override `messages()` and `attributes()` to customize validation errors.

```php
<?php

declare(strict_types=1);

namespace App\Command;

use FriendsOfHyperf\CommandValidation\Traits\ValidatesInput;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;

#[Command]
class FooCommand extends HyperfCommand
{
    use ValidatesInput;

    protected ?string $signature = 'foo:hello {?name : The name of the person to greet.}';

    public function handle(): void
    {
        $this->info(sprintf('Hello %s.', $this->input->getArgument('name')));
    }

    protected function rules(): array
    {
        return [
            'name' => 'required',
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'The :attribute field is required.',
        ];
    }

    protected function attributes(): array
    {
        return [
            'name' => 'recipient name',
        ];
    }
}
```

## Validation Behavior

Hyperf discovers the trait's `setUpValidatesInput()` hook and runs it before
the command executes. The component:

1. Returns immediately when `rules()` is empty.
2. Merges all command arguments and options into the validation data. If an
   argument and an option have the same key, the option value takes precedence.
3. Creates a validator with the rules, messages, and attribute names supplied
   by the command.
4. Calls `validate()`. Failed validation throws
   `Hyperf\Validation\ValidationException`, so the command handler does not run.

The component ignores the validated data returned by `validate()` and does not
modify the command input. The handler continues to read the original argument
and option values. If the container does not provide
`ValidatorFactoryInterface`, validation throws a `RuntimeException` asking you
to install `hyperf/validation`.

Do not call `setUpValidatesInput()` directly.

## Customization Methods

| Method | Purpose | Default |
| --- | --- | --- |
| `rules(): array` | Validation rules for command arguments and options. | `[]` |
| `messages(): array` | Custom validation messages. | `[]` |
| `attributes(): array` | Custom display names for validated fields. | `[]` |
