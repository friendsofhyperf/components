# IDE Helper

IDE Helper 元件為 Hyperf 模型及使用 `Hyperf\Macroable\Macroable` 的類生成 PHP 助手檔案。

## 安裝

```shell
composer require friendsofhyperf/ide-helper
```

Hyperf 會發現元件的 `ConfigProvider`，並自動註冊 `ide-helper:model` 和
`ide-helper:macro` 命令。元件不會發布配置檔案。

## 模型助手

為應用 `app` 目錄下找到的具體模型類生成助手：

```shell
php ./bin/hyperf.php ide-helper:model
```

命令會在當前目錄寫入 `_ide_helper_models.php`。它會生成 `Eloquent` 助手，並根據型別轉換、
訪問器、關聯關係和軟刪除推斷模型屬性及方法。

使用 `--name`（`-N`）修改輸出檔案；使用 `--ignore`（`-I`）排除以逗號分隔的完整模型類名：

```shell
php ./bin/hyperf.php ide-helper:model --name=storage/ide-models.php \
  --ignore='App\Model\InternalModel,App\Model\LegacyModel'
```

安裝建議依賴 `doctrine/dbal` 後，還可從資料庫表字段推斷屬性：

```shell
composer require --dev doctrine/dbal
```

## 宏助手

為已載入的宏生成助手：

```shell
php ./bin/hyperf.php ide-helper:macro
```

掃描 Composer 最佳化類對映前，命令會執行 `composer dump-autoload -o --no-scripts`。預設會在
當前目錄寫入 `_ide_helper_macros.php`。

使用 `--name`（`-N`）修改輸出檔案：

```shell
php ./bin/hyperf.php ide-helper:macro --name=storage/ide-macros.php
```

## 配置

需要自定義生成行為時，請建立 `config/autoload/ide-helper.php`：

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

- `model.ignores`：模型生成時排除的完整模型類名。
- `model.camel_case_properties`：將生成的屬性名轉換為駝峰格式，預設為 `false`。
- `model.type_overrides`：將推斷出的模型屬性型別對映為替代型別。
- `model.custom_db_types.<platform>`：將自定義資料庫型別對映為 Doctrine 型別。
- `macro.namespaces`：只掃描類名以其中任一字首開頭的類對映條目；空陣列會掃描全部條目。
- `macro.rejects`：宏生成時排除的完整類名。
