# Command Validation

在 Hyperf 命令執行前驗證其參數和選項。

## 安裝

```shell
composer require friendsofhyperf/command-validation
```

該元件要求 Hyperf `~3.2.0`（包括 `hyperf/validation`），且未宣告選用依賴。
Hyperf 會自動探索該元件的空 `ConfigProvider`，因此該元件沒有需要發布的設定。

## 使用

在命令中引入 `ValidatesInput` trait 並覆寫 `rules()`。也可以覆寫
`messages()` 和 `attributes()` 來自訂驗證錯誤。

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

Hyperf 會發現 trait 的 `setUpValidatesInput()` 掛鉤，並在命令執行前呼叫。
該元件會：

1. 在 `rules()` 為空時立即返回。
2. 將命令的全部參數和選項合併為待驗證資料。如果參數與選項使用相同鍵名，
   選項值優先。
3. 使用命令提供的規則、訊息和屬性名稱建立驗證器。
4. 呼叫 `validate()`。驗證失敗會擲回
   `Hyperf\Validation\ValidationException`，因此命令處理方法不會執行。

該元件會忽略 `validate()` 回傳的已驗證資料，也不會修改命令輸入；處理方法讀取的
仍是原始參數和選項值。如果容器未提供 `ValidatorFactoryInterface`，驗證會擲回
`RuntimeException`，提示安裝 `hyperf/validation`。

請勿直接呼叫 `setUpValidatesInput()`。

## 自訂方法

| 方法 | 用途 | 預設值 |
| --- | --- | --- |
| `rules(): array` | 命令參數和選項的驗證規則。 | `[]` |
| `messages(): array` | 自訂驗證訊息。 | `[]` |
| `attributes(): array` | 待驗證欄位的自訂顯示名稱。 | `[]` |
