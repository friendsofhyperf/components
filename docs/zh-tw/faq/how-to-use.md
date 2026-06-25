# 如何使用元件

## 安裝元件包

從[元件目錄](../components/index.md)選擇元件，然後使用 Composer 安裝：

```shell
composer require friendsofhyperf/cache
```

## 檢查可選依賴

閱讀元件的 `composer.json` 和文件。可選功能可能需要 Composer `suggest` 段中列出的包。

## 釋出配置

元件提供可釋出配置時，請使用文件說明的命令：

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/cache
```

不要直接對所有元件執行此命令，並非每個元件都有可釋出配置。

## 驗證整合

啟動應用並實際使用整合功能，同時新增應用級測試。排查問題時，請在 Issue 中提供元件版本和
Hyperf 版本。
