# 如何使用组件

## 安装组件包

从[组件目录](../components/index.md)选择组件，然后使用 Composer 安装：

```shell
composer require friendsofhyperf/cache
```

## 检查可选依赖

阅读组件的 `composer.json` 和文档。可选功能可能需要 Composer `suggest` 段中列出的包。

## 发布配置

组件提供可发布配置时，请使用文档说明的命令：

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/cache
```

不要直接对所有组件运行此命令，并非每个组件都有可发布配置。

## 验证集成

启动应用并实际使用集成功能，同时添加应用级测试。排查问题时，请在 Issue 中提供组件版本和
Hyperf 版本。
