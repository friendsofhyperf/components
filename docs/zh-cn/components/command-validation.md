# Command Validation

在 Hyperf 命令运行前验证其参数和选项。

## 安装

```shell
composer require friendsofhyperf/command-validation
```

该组件要求 Hyperf `~3.2.0`（包括 `hyperf/validation`），且未声明可选依赖。
Hyperf 会自动发现该组件的空 `ConfigProvider`，因此该组件没有需要发布的配置。

## 使用

在命令中引入 `ValidatesInput` trait 并重写 `rules()`。也可以重写
`messages()` 和 `attributes()` 来自定义验证错误。

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

## 验证行为

Hyperf 会发现 trait 的 `setUpValidatesInput()` 钩子，并在命令执行前调用。
该组件会：

1. 在 `rules()` 为空时立即返回。
2. 将命令的全部参数和选项合并为待验证数据。如果参数与选项使用相同键名，
   选项值优先。
3. 使用命令提供的规则、消息和属性名称创建验证器。
4. 调用 `validate()`。验证失败会抛出
   `Hyperf\Validation\ValidationException`，因此命令处理方法不会运行。

该组件会忽略 `validate()` 返回的已验证数据，也不会修改命令输入；处理方法读取的
仍是原始参数和选项值。如果容器未提供 `ValidatorFactoryInterface`，验证会抛出
`RuntimeException`，提示安装 `hyperf/validation`。

请勿直接调用 `setUpValidatesInput()`。

## 自定义方法

| 方法 | 用途 | 默认值 |
| --- | --- | --- |
| `rules(): array` | 命令参数和选项的验证规则。 | `[]` |
| `messages(): array` | 自定义验证消息。 | `[]` |
| `attributes(): array` | 待验证字段的自定义显示名称。 | `[]` |
