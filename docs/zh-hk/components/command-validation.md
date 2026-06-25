# Command Validation

在 Hyperf 命令運行前驗證其參數和選項。

## 安裝

```shell
composer require friendsofhyperf/command-validation
```

該組件要求 Hyperf `~3.2.0`（包括 `hyperf/validation`），且未聲明可選依賴。
Hyperf 會自動發現該組件的空 `ConfigProvider`，因此該組件沒有需要發佈的配置。

## 使用

在命令中引入 `ValidatesInput` trait 並重寫 `rules()`。也可以重寫
`messages()` 和 `attributes()` 來自定義驗證錯誤。

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

## 驗證行為

Hyperf 會發現 trait 的 `setUpValidatesInput()` 鈎子，並在命令執行前調用。
該組件會：

1. 在 `rules()` 為空時立即返回。
2. 將命令的全部參數和選項合併為待驗證數據。如果參數與選項使用相同鍵名，
   選項值優先。
3. 使用命令提供的規則、消息和屬性名稱創建驗證器。
4. 調用 `validate()`。驗證失敗會拋出
   `Hyperf\Validation\ValidationException`，因此命令處理方法不會運行。

該組件會忽略 `validate()` 返回的已驗證數據，也不會修改命令輸入；處理方法讀取的
仍是原始參數和選項值。如果容器未提供 `ValidatorFactoryInterface`，驗證會拋出
`RuntimeException`，提示安裝 `hyperf/validation`。

請勿直接調用 `setUpValidatesInput()`。

## 自定義方法

| 方法 | 用途 | 默認值 |
| --- | --- | --- |
| `rules(): array` | 命令參數和選項的驗證規則。 | `[]` |
| `messages(): array` | 自定義驗證消息。 | `[]` |
| `attributes(): array` | 待驗證字段的自定義顯示名稱。 | `[]` |
