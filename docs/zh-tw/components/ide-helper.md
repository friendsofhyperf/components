# IDE Helper

IDE Helper 元件為 Hyperf 模型及使用 `Hyperf\Macroable\Macroable` 的類別產生 PHP 助手檔案。

## 安裝

```shell
composer require friendsofhyperf/ide-helper
```

Hyperf 會發現元件的 `ConfigProvider`，並自動註冊 `ide-helper:model` 和
`ide-helper:macro` 命令。元件不會發布設定檔。

## 模型助手

為應用程式 `app` 目錄下找到的具體模型類別產生助手：

```shell
php ./bin/hyperf.php ide-helper:model
```

命令會在目前目錄寫入 `_ide_helper_models.php`。它會產生 `Eloquent` 助手，並根據型別轉換、
存取器、關聯關係和軟刪除推斷模型屬性及方法。

使用 `--name`（`-N`）修改輸出檔案；使用 `--ignore`（`-I`）排除以逗號分隔的完整模型類別名稱：

```shell
php ./bin/hyperf.php ide-helper:model --name=storage/ide-models.php \
  --ignore='App\Model\InternalModel,App\Model\LegacyModel'
```

安裝建議依賴 `doctrine/dbal` 後，還可從資料庫資料表欄位推斷屬性：

```shell
composer require --dev doctrine/dbal
```

## 巨集助手

為已載入的巨集產生助手：

```shell
php ./bin/hyperf.php ide-helper:macro
```

掃描 Composer 最佳化類別映射前，命令會執行 `composer dump-autoload -o --no-scripts`。預設會在
目前目錄寫入 `_ide_helper_macros.php`。

使用 `--name`（`-N`）修改輸出檔案：

```shell
php ./bin/hyperf.php ide-helper:macro --name=storage/ide-macros.php
```

## 設定

需要自訂產生行為時，請建立 `config/autoload/ide-helper.php`：

```php
<?php

return [
    'model' => [
        'ignores' => [
            App\Model\InternalModel::class,
        ],
        'camel_case_properties' => false,
        'type_overrides' => [
            'integer' => 'int',
        ],
        'custom_db_types' => [
            'mysql' => [
                'tinyint' => 'integer',
            ],
        ],
    ],
    'macro' => [
        'namespaces' => [
            'App\\',
        ],
        'rejects' => [
            App\Example\RejectedMacroable::class,
        ],
    ],
];
```

- `model.ignores`：模型產生時排除的完整模型類別名稱。
- `model.camel_case_properties`：將產生的屬性名稱轉換為駝峰格式，預設為 `false`。
- `model.type_overrides`：將推斷出的模型屬性型別映射為替代型別。
- `model.custom_db_types.<platform>`：將自訂資料庫型別映射為 Doctrine 型別。
- `macro.namespaces`：只掃描類別名稱以其中任一前綴開頭的類別映射項目；空陣列會掃描全部項目。
- `macro.rejects`：巨集產生時排除的完整類別名稱。
