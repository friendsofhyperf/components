# Command Validation

在 Hyperf 命令執行前驗證其引數和選項。

## 安裝

```shell
composer require friendsofhyperf/command-validation
```

該元件要求 Hyperf `~3.2.0`（包括 `hyperf/validation`），且未宣告可選依賴。
Hyperf 會自動發現該元件的空 `ConfigProvider`，因此該元件沒有需要釋出的配置。

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

Hyperf 會發現 trait 的 `setUpValidatesInput()` 鉤子，並在命令執行前呼叫。
該元件會：

1. 在 `rules()` 為空時立即返回。
2. 將命令的全部引數和選項合併為待驗證資料。如果引數與選項使用相同鍵名，
   選項值優先。
3. 使用命令提供的規則、訊息和屬性名稱建立驗證器。
4. 呼叫 `validate()`。驗證失敗會丟擲
   `Hyperf\Validation\ValidationException`，因此命令處理方法不會執行。

該元件會忽略 `validate()` 返回的已驗證資料，也不會修改命令輸入；處理方法讀取的
仍是原始引數和選項值。如果容器未提供 `ValidatorFactoryInterface`，驗證會丟擲
`RuntimeException`，提示安裝 `hyperf/validation`。

請勿直接呼叫 `setUpValidatesInput()`。

## 自定義方法

| 方法 | 用途 | 預設值 |
| --- | --- | --- |
| `rules(): array` | 命令引數和選項的驗證規則。 | `[]` |
| `messages(): array` | 自定義驗證訊息。 | `[]` |
| `attributes(): array` | 待驗證欄位的自定義顯示名稱。 | `[]` |
