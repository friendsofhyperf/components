# 如何使用組件

## 安裝組件包

從[組件目錄](../components/index.md)選擇組件，然後使用 Composer 安裝：

```shell
composer require friendsofhyperf/cache
```

## 檢查可選依賴

閲讀組件的 `composer.json` 和文檔。可選功能可能需要 Composer `suggest` 段中列出的包。

## 發佈配置

組件提供可發佈配置時，請使用文檔説明的命令：

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/cache
```

不要直接對所有組件運行此命令，並非每個組件都有可發佈配置。

## 驗證集成

啓動應用並實際使用集成功能，同時添加應用級測試。排查問題時，請在 Issue 中提供組件版本和
Hyperf 版本。
