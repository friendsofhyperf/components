# IDE Helper

IDE Helper 組件為 Hyperf 模型及使用 `Hyperf\Macroable\Macroable` 的類生成 PHP 助手文件。

## 安裝

```shell
composer require friendsofhyperf/ide-helper
```

Hyperf 會發現組件的 `ConfigProvider`，並自動註冊 `ide-helper:model` 和
`ide-helper:macro` 命令。組件不會發布配置文件。

## 模型助手

為應用 `app` 目錄下找到的具體模型類生成助手：

```shell
php ./bin/hyperf.php ide-helper:model
```

命令會在當前目錄寫入 `_ide_helper_models.php`。它會生成 `Eloquent` 助手，並根據類型轉換、
訪問器、關聯關係和軟刪除推斷模型屬性及方法。

使用 `--name`（`-N`）修改輸出文件；使用 `--ignore`（`-I`）排除以逗號分隔的完整模型類名：

```shell
php ./bin/hyperf.php ide-helper:model --name=storage/ide-models.php \
  --ignore='App\Model\InternalModel,App\Model\LegacyModel'
```

安裝建議依賴 `doctrine/dbal` 後，還可從數據庫表字段推斷屬性：

```shell
composer require --dev doctrine/dbal
```

## 宏助手

為已加載的宏生成助手：

```shell
php ./bin/hyperf.php ide-helper:macro
```

掃描 Composer 優化類映射前，命令會運行 `composer dump-autoload -o --no-scripts`。默認會在
當前目錄寫入 `_ide_helper_macros.php`。

使用 `--name`（`-N`）修改輸出文件：

```shell
php ./bin/hyperf.php ide-helper:macro --name=storage/ide-macros.php
```

## 配置

需要自定義生成行為時，請創建 `config/autoload/ide-helper.php`：

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
- `model.camel_case_properties`：將生成的屬性名轉換為駝峯格式，默認為 `false`。
- `model.type_overrides`：將推斷出的模型屬性類型映射為替代類型。
- `model.custom_db_types.<platform>`：將自定義數據庫類型映射為 Doctrine 類型。
- `macro.namespaces`：只掃描類名以其中任一前綴開頭的類映射條目；空數組會掃描全部條目。
- `macro.rejects`：宏生成時排除的完整類名。
