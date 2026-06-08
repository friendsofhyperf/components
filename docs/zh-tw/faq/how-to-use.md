# 如何使用元件

## 安裝元件套件

從[元件目錄](../components/index.md)選擇元件，然後使用 Composer 安裝：

```shell
composer require friendsofhyperf/cache
```

## 檢查選用相依套件

閱讀元件的 `composer.json` 和文件。選用功能可能需要 Composer `suggest` 區段中列出的套件。

## 發布設定

元件提供可發布設定時，請使用文件說明的指令：

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/cache
```

不要直接對所有元件執行此指令，並非每個元件都有可發布設定。

## 驗證整合

啟動應用程式並實際使用整合功能，同時加入應用程式層級測試。排查問題時，請在 Issue 中提供
元件版本和 Hyperf 版本。
