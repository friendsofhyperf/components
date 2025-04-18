# Command Validation

用于 Hyperf 命令行的验证组件。

## 安装

```shell
composer require friendsofhyperf/command-validation
```

## 使用

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

    /**
     * 执行的命令行
     */
    protected string $name = 'foo:hello {?name : The name of the person to greet.}';

    public function handle()
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
            'name.required' => 'The name is required.',
        ];
    }
}
```
