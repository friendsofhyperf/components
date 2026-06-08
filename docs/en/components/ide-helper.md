# IDE Helper

The IDE Helper component generates PHP helper files for Hyperf models and classes that use
`Hyperf\Macroable\Macroable`.

## Installation

```shell
composer require friendsofhyperf/ide-helper
```

Hyperf discovers the component's `ConfigProvider` and automatically registers the
`ide-helper:model` and `ide-helper:macro` commands. The component does not publish a configuration
file.

## Model Helper

Generate a helper for concrete model classes found under the application's `app` directory:

```shell
php ./bin/hyperf.php ide-helper:model
```

The command writes `_ide_helper_models.php` in the current directory. It generates an `Eloquent`
helper plus model properties and methods inferred from casts, accessors, relations, and soft
deletes.

Use `--name` (`-N`) to change the output file, and `--ignore` (`-I`) to exclude a comma-separated
list of exact model class names:

```shell
php ./bin/hyperf.php ide-helper:model --name=storage/ide-models.php \
  --ignore='App\Model\InternalModel,App\Model\LegacyModel'
```

Install the suggested `doctrine/dbal` dependency to also infer properties from database table
columns:

```shell
composer require --dev doctrine/dbal
```

## Macro Helper

Generate a helper for loaded macros:

```shell
php ./bin/hyperf.php ide-helper:macro
```

Before scanning Composer's optimized class map, the command runs
`composer dump-autoload -o --no-scripts`. It writes `_ide_helper_macros.php` in the current
directory by default.

Use `--name` (`-N`) to change the output file:

```shell
php ./bin/hyperf.php ide-helper:macro --name=storage/ide-macros.php
```

## Configuration

Create `config/autoload/ide-helper.php` when you need to customize generation:

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

- `model.ignores`: exact model class names excluded from model generation.
- `model.camel_case_properties`: converts generated property names to camel case; defaults to
  `false`.
- `model.type_overrides`: maps inferred model property types to replacement types.
- `model.custom_db_types.<platform>`: maps custom database types to Doctrine types.
- `macro.namespaces`: only scans class-map entries whose class name starts with one of these
  prefixes; an empty array scans all entries.
- `macro.rejects`: exact class names excluded from macro generation.
