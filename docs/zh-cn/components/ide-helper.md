# IDE Helper

IDE Helper 组件为 Hyperf 模型及使用 `Hyperf\Macroable\Macroable` 的类生成 PHP 助手文件。

## 安装

```shell
composer require friendsofhyperf/ide-helper
```

Hyperf 会发现组件的 `ConfigProvider`，并自动注册 `ide-helper:model` 和
`ide-helper:macro` 命令。组件不会发布配置文件。

## 模型助手

为应用 `app` 目录下找到的具体模型类生成助手：

```shell
php ./bin/hyperf.php ide-helper:model
```

命令会在当前目录写入 `_ide_helper_models.php`。它会生成 `Eloquent` 助手，并根据类型转换、
访问器、关联关系和软删除推断模型属性及方法。

使用 `--name`（`-N`）修改输出文件；使用 `--ignore`（`-I`）排除以逗号分隔的完整模型类名：

```shell
php ./bin/hyperf.php ide-helper:model --name=storage/ide-models.php \
  --ignore='App\Model\InternalModel,App\Model\LegacyModel'
```

安装建议依赖 `doctrine/dbal` 后，还可从数据库表字段推断属性：

```shell
composer require --dev doctrine/dbal
```

## 宏助手

为已加载的宏生成助手：

```shell
php ./bin/hyperf.php ide-helper:macro
```

扫描 Composer 优化类映射前，命令会运行 `composer dump-autoload -o --no-scripts`。默认会在
当前目录写入 `_ide_helper_macros.php`。

使用 `--name`（`-N`）修改输出文件：

```shell
php ./bin/hyperf.php ide-helper:macro --name=storage/ide-macros.php
```

## 配置

需要自定义生成行为时，请创建 `config/autoload/ide-helper.php`：

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

- `model.ignores`：模型生成时排除的完整模型类名。
- `model.camel_case_properties`：将生成的属性名转换为驼峰格式，默认为 `false`。
- `model.type_overrides`：将推断出的模型属性类型映射为替代类型。
- `model.custom_db_types.<platform>`：将自定义数据库类型映射为 Doctrine 类型。
- `macro.namespaces`：只扫描类名以其中任一前缀开头的类映射条目；空数组会扫描全部条目。
- `macro.rejects`：宏生成时排除的完整类名。
