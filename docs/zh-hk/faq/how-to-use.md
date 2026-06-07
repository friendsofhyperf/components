# 如何使用組件

## 安裝組件套件

從[組件目錄](../components/index.md)選擇組件，然後使用 Composer 安裝：

```shell
composer require friendsofhyperf/cache
```

## 檢查可選依賴

閱讀組件的 `composer.json` 和文件。可選功能可能需要 Composer `suggest` 段中列出的套件。

## 發佈設定

組件提供可發佈設定時，請使用文件說明的指令：

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/cache
```

不要直接對所有組件執行此指令，並非每個組件都有可發佈設定。

## 驗證整合

啟動應用程式並實際使用整合功能，同時加入應用程式層級測試。排查問題時，請在 Issue 中提供
組件版本和 Hyperf 版本。
